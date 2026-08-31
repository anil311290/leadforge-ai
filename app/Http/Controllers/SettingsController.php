<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AuditService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get();

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->all();

        foreach ($data as $key => $value) {
            if (str_starts_with($key, '_')) {
                continue;
            }
            if (is_string($value) && str_contains($key, 'key') && ! $value) {
                continue; // don't overwrite secrets with empty
            }
            Setting::set($key, $value);
        }

        AuditService::record(auth()->user(), 'settings_updated', 'Setting');

        if ($request->wantsJson()) {
            return response()->json(['success' => 'Settings saved.']);
        }

        return back()->with('success', 'Settings saved.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->filled('entity'), fn ($q) => $q->where('entity', $request->input('entity')))
            ->when($request->filled('q'), fn ($q) => $q->where('action', 'like', '%'.$request->input('q').'%'))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('audit.index', compact('logs'));
    }
}
<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index');
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (! empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }
        $user->save();

        AuditService::record($user, 'profile_updated', 'User', $user->id);

        return back()->with('success', 'Profile updated.');
    }
}
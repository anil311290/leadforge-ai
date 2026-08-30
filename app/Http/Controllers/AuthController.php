<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request), 300);

            return back()->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();
        RateLimiter::clear($this->throttleKey($request));

        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        AuditService::record($user, 'user_login', 'User');

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $data['password'] = bcrypt($data['password']);
        $data['role'] = 'user';

        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        AuditService::record($user, 'user_registered', 'User');

        return redirect()->route('dashboard')->with('success', 'Welcome to LeadForge AI.');
    }

    public function logout(Request $request)
    {
        AuditService::record(Auth::user(), 'user_logout', 'User');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $this->validate($request, ['email' => ['required', 'email']]);

        return back()->with('status', 'If that email exists, a password reset link has been sent.');
    }

    protected function throttleKey(Request $request): string
    {
        return 'login:'.strtolower((string) $request->input('email')).'|'.$request->ip();
    }
}
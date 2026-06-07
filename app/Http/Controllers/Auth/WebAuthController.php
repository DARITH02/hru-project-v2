<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\WebLoginRequest;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function showLogin() { return view('auth.login'); }

    public function login(WebLoginRequest $request)
    {
        $user = $this->auth->attempt($request->loginIdentifier(), (string) $request->password);

        if ($user && !$user->is_approved && $user->role !== 'student') {
            return back()->withErrors(['email' => 'Your account is pending approval by a Superadmin.'])->onlyInput('email');
        }

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended(route('admin.students.overview'));
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->onlyInput('email');
    }

    public function demoLogin(Request $request)
    {
        $user = User::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('demo123'),
                'role' => 'admin',
                'is_approved' => true,
            ]
        );

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.students.overview');
    }

    public function showRegister() { return view('auth.register'); }

    public function register(RegisterRequest $request)
    {
        $role = 'admin';
        $isApproved = false;

        if ($request->filled('admin_key')) {
            $superAdminKey = config('app.super_admin_key');
            if ($superAdminKey && hash_equals($superAdminKey, $request->admin_key)) {
                $role = 'super_admin';
                $isApproved = true;
            } else {
                return back()->withErrors(['admin_key' => 'Invalid super admin key. Leave blank for normal Admin.'])->withInput();
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'is_approved' => $isApproved,
        ]);

        if (!$isApproved) {
            return redirect()->route('login')->with('success', 'Registration successful! Your account is pending Superadmin approval.');
        }

        Auth::login($user);
        return redirect()->route('admin.students.overview');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

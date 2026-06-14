<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\WebLoginRequest;
use App\Models\Setting;
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

    public function showLogin()
    {
        $branding = $this->branding();

        return view('auth.login', compact('branding'));
    }

    public function login(WebLoginRequest $request)
    {
        $user = $this->auth->attempt($request->loginIdentifier(), (string) $request->password);

        if ($user && !$user->is_approved && $user->role !== 'student') {
            return back()->withErrors(['email' => 'Your account is pending approval by a Superadmin.'])->onlyInput('email');
        }

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();

            $defaultRoute = match ($user->role) {
                'teacher' => route('teacher.attendance'),
                'student' => route('admin.students.overview'),
                default => route('admin.dashboard'),
            };

            return redirect()->intended($defaultRoute);
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->onlyInput('email');
    }

    public function demoLogin(Request $request)
    {
        abort_unless(config('auth.demo_login_enabled'), 404);

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

        return redirect()->route('admin.dashboard');
    }

    public function showRegister()
    {
        abort_unless(config('auth.public_registration_enabled'), 404);

        $branding = $this->branding();

        return view('auth.register', compact('branding'));
    }

    public function register(RegisterRequest $request)
    {
        abort_unless(config('auth.public_registration_enabled'), 404);

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
        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    private function branding(): array
    {
        return [
            'app_name' => Setting::get('app_name', 'HRU ATS'),
            'app_sub' => Setting::get('app_sub', 'Attendance Tracking System'),
            'institution_name' => Setting::get('institution_name', 'HRU'),
            'app_logo' => Setting::get('app_logo', 'https://res.cloudinary.com/dnrblpkal/image/upload/q_auto/f_auto/v1775536855/branding/k6obqtagifkszo8pehnd.png'),
        ];
    }
}

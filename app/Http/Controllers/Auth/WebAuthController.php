<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\WebLoginRequest;
use App\Models\Department;
use App\Models\Setting;
use App\Models\TeacherRegistrationRequest;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

    public function showForgotPassword()
    {
        $branding = $this->branding();

        return view('auth.forgot_password', compact('branding'));
    }

    public function sendPasswordResetOtp(Request $request)
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $data = Validator::make($request->all(), [
            'email' => 'required|email:rfc',
        ])->validate();

        $user = User::whereRaw('LOWER(email) = ?', [$data['email']])
            ->whereIn('role', ['admin', 'super_admin'])
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Only admin and super admin accounts can reset password here.',
            ]);
        }

        $code = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        $request->session()->put('password_reset_email_otp', [
            'email' => strtolower($user->email),
            'hash' => Hash::make($code),
            'expires_at' => $expiresAt->toIso8601String(),
            'attempts' => 0,
        ]);

        $this->sendOtpMail(
            to: $user->email,
            subject: 'Password reset verification code',
            title: 'Reset your password',
            intro: 'Use this verification code to reset your administrator password.',
            code: $code,
            expiresAt: $expiresAt,
        );

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent. Check your email inbox.',
            'expires_at' => $expiresAt->toIso8601String(),
            'reset_url' => route('password.reset', ['email' => $user->email]),
        ]);
    }

    public function showResetPassword(Request $request)
    {
        $branding = $this->branding();
        $email = $request->query('email', $request->session()->get('password_reset_email_otp.email'));

        return view('auth.reset_password', compact('branding', 'email'));
    }

    public function resetPassword(Request $request)
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $data = Validator::make($request->all(), [
            'email' => 'required|email:rfc',
            'email_otp' => 'required|digits:6',
            'password' => 'required|min:8|confirmed',
        ])->validate();

        $this->assertPasswordResetOtpVerified($request);

        $user = User::whereRaw('LOWER(email) = ?', [$data['email']])
            ->whereIn('role', ['admin', 'super_admin'])
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Only admin and super admin accounts can reset password here.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => null,
        ])->save();

        $request->session()->forget('password_reset_email_otp');

        return redirect()->route('login')->with('success', 'Password reset successfully. Sign in with your new password.');
    }

    public function showRegister(Request $request)
    {
        $isTeacherInvite = $request->query('role') === 'teacher' && $request->hasValidSignature();
        abort_unless(config('auth.public_registration_enabled') || $isTeacherInvite, 404);

        $branding = $this->branding();
        $registrationRole = $isTeacherInvite ? 'teacher' : 'admin';
        $departments = $isTeacherInvite ? Department::orderBy('name')->get() : collect();

        return view('auth.register', compact('branding', 'registrationRole', 'departments'));
    }

    public function sendRegistrationOtp(Request $request)
    {
        abort_unless(config('auth.public_registration_enabled') || $request->input('role') === 'teacher', 404);

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $data = Validator::make($request->all(), [
            'email' => 'required|email:rfc|unique:users,email',
            'role' => 'nullable|in:teacher',
        ])->validate();

        $code = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        $request->session()->put('registration_email_otp', [
            'email' => strtolower($data['email']),
            'hash' => Hash::make($code),
            'expires_at' => $expiresAt->toIso8601String(),
            'attempts' => 0,
        ]);

        $this->sendOtpMail(
            to: $data['email'],
            subject: 'Registration email verification code',
            title: 'Verify your email address',
            intro: 'Use this verification code to finish creating your account.',
            code: $code,
            expiresAt: $expiresAt,
        );

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent. Check your email inbox.',
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $isTeacherInvite = $request->input('role') === 'teacher' && $request->hasValidSignature();
        abort_unless(config('auth.public_registration_enabled') || $isTeacherInvite, 404);

        $this->assertRegistrationOtpVerified($request);

        if ($isTeacherInvite) {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'role' => 'teacher',
                    'is_approved' => false,
                    'email_verified_at' => now(),
                ]);

                TeacherRegistrationRequest::create([
                    'user_id' => $user->id,
                    'department_id' => $request->department_id,
                    'specialization' => $request->specialization,
                    'status' => 'pending',
                ]);
            });

            $request->session()->forget('registration_email_otp');

            return redirect()->route('login')->with('success', 'Teacher registration submitted. Your teacher profile will be added to the system after Superadmin approval.');
        }

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
            'email_verified_at' => now(),
        ]);

        $request->session()->forget('registration_email_otp');

        if (!$isApproved) {
            return redirect()->route('login')->with('success', 'Registration successful! Your account is pending Superadmin approval.');
        }

        Auth::login($user);
        return redirect()->route('admin.dashboard');
    }

    private function assertRegistrationOtpVerified(Request $request): void
    {
        $otp = $request->session()->get('registration_email_otp');

        if (!$otp || strtolower((string) $otp['email']) !== strtolower((string) $request->email)) {
            throw ValidationException::withMessages([
                'email_otp' => 'Send a verification code to this email before creating the account.',
            ]);
        }

        if (now()->greaterThan($otp['expires_at'])) {
            $request->session()->forget('registration_email_otp');

            throw ValidationException::withMessages([
                'email_otp' => 'The verification code expired. Send a new code.',
            ]);
        }

        if (($otp['attempts'] ?? 0) >= 5) {
            $request->session()->forget('registration_email_otp');

            throw ValidationException::withMessages([
                'email_otp' => 'Too many incorrect verification attempts. Send a new code.',
            ]);
        }

        if (!Hash::check((string) $request->email_otp, (string) $otp['hash'])) {
            $otp['attempts'] = ($otp['attempts'] ?? 0) + 1;
            $request->session()->put('registration_email_otp', $otp);

            throw ValidationException::withMessages([
                'email_otp' => 'The verification code is incorrect.',
            ]);
        }
    }

    private function assertPasswordResetOtpVerified(Request $request): void
    {
        $otp = $request->session()->get('password_reset_email_otp');

        if (!$otp || strtolower((string) $otp['email']) !== strtolower((string) $request->email)) {
            throw ValidationException::withMessages([
                'email_otp' => 'Send a password reset code to this email first.',
            ]);
        }

        if (now()->greaterThan($otp['expires_at'])) {
            $request->session()->forget('password_reset_email_otp');

            throw ValidationException::withMessages([
                'email_otp' => 'The verification code expired. Send a new code.',
            ]);
        }

        if (($otp['attempts'] ?? 0) >= 5) {
            $request->session()->forget('password_reset_email_otp');

            throw ValidationException::withMessages([
                'email_otp' => 'Too many incorrect verification attempts. Send a new code.',
            ]);
        }

        if (!Hash::check((string) $request->email_otp, (string) $otp['hash'])) {
            $otp['attempts'] = ($otp['attempts'] ?? 0) + 1;
            $request->session()->put('password_reset_email_otp', $otp);

            throw ValidationException::withMessages([
                'email_otp' => 'The verification code is incorrect.',
            ]);
        }
    }

    private function sendOtpMail(string $to, string $subject, string $title, string $intro, string $code, $expiresAt): void
    {
        $branding = $this->branding();

        Mail::send('emails.otp', [
            'appName' => $branding['app_name'],
            'appSub' => $branding['app_sub'],
            'institutionName' => $branding['institution_name'],
            'appLogo' => $branding['app_logo'],
            'title' => $title,
            'intro' => $intro,
            'code' => $code,
            'expiresAt' => $expiresAt,
            'appUrl' => config('app.url'),
        ], function ($message) use ($to, $subject) {
            $message
                ->to($to)
                ->subject($subject);
        });
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
            'app_name' => Setting::get('app_name', 'HRU-ATMS'),
            'app_sub' => Setting::get('app_sub', 'Management System'),
            'institution_name' => Setting::get('institution_name', 'HRU'),
            'app_logo' => Setting::get('app_logo', 'https://res.cloudinary.com/dnrblpkal/image/upload/q_auto/f_auto/v1775536855/branding/k6obqtagifkszo8pehnd.png'),
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Models\Setting;
use App\Services\Auth\AuthService;
use App\Support\Http\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function login(LoginRequest $request)
    {
        $user = $this->auth->attempt($request->loginIdentifier(), (string) $request->password);

        if (!$user) {
            return $this->invalidCredentialsResponse();
        }

        $deviceName = $request->device_name ?? 'web-dashboard';
        $token = $user->createToken($deviceName)->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'student' => $this->auth->studentPayload($user),
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success([
            'message' => 'Logged out successfully'
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'student' => $this->auth->studentPayload($user),
        ]);
    }

    public function branding()
    {
        return ApiResponse::success([
            'app_name' => Setting::get('app_name', 'HRU'),
            'app_sub' => Setting::get('app_sub'),
            'system_name' => Setting::get('system_name'),
            'app_logo' => Setting::get('app_logo', 'https://res.cloudinary.com/dnrblpkal/image/upload/q_auto/f_auto/v1775536855/branding/k6obqtagifkszo8pehnd.png'),
            'campus_lat' => Setting::get('campus_lat', '11.524012'),
            'campus_lng' => Setting::get('campus_lng', '104.876273'),
            'campus_radius_meters' => Setting::get('campus_radius_meters', '250'),
            'require_location' => Setting::get('require_location', 'true') === 'true',
        ]);
    }

    private function invalidCredentialsResponse(): \Illuminate\Http\JsonResponse
    {
        return ApiResponse::error(
            'The provided credentials are incorrect.',
            401,
            [
                'email' => ['The provided credentials are incorrect.'],
            ]
        );
    }
}

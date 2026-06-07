<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreUserLocationRequest;
use App\Services\UserLocationService;
use App\Support\Http\ApiResponse;
use Illuminate\Support\Facades\Log;

class UserLocationController extends Controller
{
    public function __construct(private readonly UserLocationService $locations)
    {
    }

    public function store(StoreUserLocationRequest $request)
    {
        try {
            $location = $this->locations->storeFromRequest($request);

            return ApiResponse::success([
                'status' => true,
                'message' => 'Location saved successfully',
                'data' => $location,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to save user location.', [
                'user_id' => $request->user()?->id,
                'exception' => $e,
            ]);

            return ApiResponse::error('Failed to save location', 500, [], [
                'status' => false,
                'error' => 'Unable to save location at this time.',
            ]);
        }
    }
}

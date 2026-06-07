<?php

namespace App\Services;

use App\Http\Requests\Api\StoreUserLocationRequest;
use App\Models\UserLocation;
use App\Repositories\UserLocationRepository;

class UserLocationService
{
    public function __construct(private readonly UserLocationRepository $locations)
    {
    }

    public function storeFromRequest(StoreUserLocationRequest $request): UserLocation
    {
        return $this->locations->create([
            'user_id' => $request->user()?->id,
            'latitude' => $request->validated('latitude'),
            'longitude' => $request->validated('longitude'),
            'accuracy' => $request->validated('accuracy'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);
    }
}

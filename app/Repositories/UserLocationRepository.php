<?php

namespace App\Repositories;

use App\Models\UserLocation;

class UserLocationRepository
{
    public function create(array $data): UserLocation
    {
        return UserLocation::create($data);
    }
}

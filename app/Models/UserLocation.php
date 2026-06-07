<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'accuracy',
        'ip_address',
        'user_agent',
    ];

    /**
     * Relationship with User if logged in.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the location data is reliable based on accuracy.
     * 
     * @param float $threshold Accuracy in meters (default 100m)
     * @return bool
     */
    public function isAccurate($threshold = 100)
    {
        return $this->accuracy !== null && $this->accuracy <= $threshold;
    }

    /**
     * Calculate distance to a specific coordinate in meters using Haversine formula.
     * 
     * @param float $targetLat
     * @param float $targetLng
     * @return float Distance in meters
     */
    public function getDistanceTo($targetLat, $targetLng)
    {
        if (!$this->latitude || !$this->longitude) return 9999999;

        $earthRadius = 6371000; // Earth radius in meters

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo   = deg2rad($targetLat);
        $lonTo   = deg2rad($targetLng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Check if the user is within a range of a target point.
     * 
     * @param float $targetLat
     * @param float $targetLng
     * @param int $radiusInMeters
     * @return bool
     */
    public function isWithinRange($targetLat, $targetLng, $radiusInMeters = 200)
    {
        return $this->getDistanceTo($targetLat, $targetLng) <= $radiusInMeters;
    }
}

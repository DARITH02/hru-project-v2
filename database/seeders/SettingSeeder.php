<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            'app_name' => 'AttendAI',
            'institution_name' => 'Obsidian Academy',
            'app_sub' => 'MANAGEMENT SYSTEM',
            'academic_year' => '2025-2026',
            'semester' => '1',
            'grace_period' => '15',
            'absent_threshold' => '45',
            'qr_ttl' => '30',
            'campus_lat' => '11.524012',
            'campus_lng' => '104.876273',
            'campus_radius_meters' => '250',
            'require_location' => 'true',
        ];

        foreach ($defaults as $key => $value) {
            \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}

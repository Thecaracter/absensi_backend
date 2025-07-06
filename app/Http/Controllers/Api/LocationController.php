<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfficeLocation;
use App\Models\LocationSetting;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    // API untuk Flutter: Get location configuration
    public function getLocationConfig()
    {
        try {
            // Get office location (main office)
            $mainOffice = OfficeLocation::active()->main()->first();

            // Get branch locations
            $branches = OfficeLocation::active()->branch()->orderBy('sort_order')->get();

            // Get all settings
            $settings = LocationSetting::all()->pluck('value', 'key');

            // Build response format sesuai dengan Flutter app
            $response = [
                'office_location' => [
                    'name' => $mainOffice->name ?? 'Kantor Pusat',
                    'address' => $mainOffice->address ?? '',
                    'latitude' => $mainOffice->latitude ?? 0,
                    'longitude' => $mainOffice->longitude ?? 0,
                    'radius_meters' => $mainOffice->radius_meters ?? 800,
                    'description' => $mainOffice->description ?? '',
                ],
                'branch_locations' => $branches->map(function ($branch, $index) {
                    return [
                        'id' => $index + 1,
                        'name' => $branch->name,
                        'address' => $branch->address,
                        'latitude' => $branch->latitude,
                        'longitude' => $branch->longitude,
                        'radius_meters' => $branch->radius_meters,
                        'description' => $branch->description,
                    ];
                })->values(),
                'settings' => [
                    'enable_location_validation' => (bool) ($settings['enable_location_validation'] ?? true),
                    'default_radius_meters' => (int) ($settings['default_radius_meters'] ?? 800),
                    'allow_manual_location' => (bool) ($settings['allow_manual_location'] ?? false),
                    'strict_mode' => (bool) ($settings['strict_mode'] ?? true),
                    'auto_tracking_enabled' => (bool) ($settings['auto_tracking_enabled'] ?? true),
                    'location_update_interval_seconds' => (int) ($settings['location_update_interval_seconds'] ?? 15),
                    'accuracy_threshold_meters' => (int) ($settings['accuracy_threshold_meters'] ?? 50),
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Location configuration retrieved successfully',
                'data' => $response
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get location configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
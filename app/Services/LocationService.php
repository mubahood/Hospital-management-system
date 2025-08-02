<?php

namespace App\Services;

use App\Models\DeviceLocation;
use App\Models\Geofence;
use App\Models\Enterprise;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LocationService
{
    /**
     * Record a new device location
     */
    public function recordLocation(array $locationData)
    {
        try {
            // Validate required fields
            $requiredFields = ['user_id', 'enterprise_id', 'device_id', 'latitude', 'longitude'];
            foreach ($requiredFields as $field) {
                if (!isset($locationData[$field])) {
                    throw new \InvalidArgumentException("Missing required field: {$field}");
                }
            }

            // Validate coordinate ranges
            if ($locationData['latitude'] < -90 || $locationData['latitude'] > 90) {
                throw new \InvalidArgumentException("Invalid latitude: must be between -90 and 90");
            }
            
            if ($locationData['longitude'] < -180 || $locationData['longitude'] > 180) {
                throw new \InvalidArgumentException("Invalid longitude: must be between -180 and 180");
            }

            // Create location record
            $location = DeviceLocation::create([
                'user_id' => $locationData['user_id'],
                'enterprise_id' => $locationData['enterprise_id'],
                'device_id' => $locationData['device_id'],
                'latitude' => $locationData['latitude'],
                'longitude' => $locationData['longitude'],
                'altitude' => $locationData['altitude'] ?? null,
                'accuracy' => $locationData['accuracy'] ?? null,
                'heading' => $locationData['heading'] ?? null,
                'speed' => $locationData['speed'] ?? null,
                'location_type' => $locationData['location_type'] ?? DeviceLocation::TYPE_GPS,
                'location_timestamp' => $locationData['location_timestamp'] ?? now(),
                'additional_data' => $locationData['metadata'] ?? null,
                'is_emergency' => $locationData['is_emergency'] ?? false,
                'address' => $locationData['address'] ?? null,
                'city' => $locationData['city'] ?? null,
                'country' => $locationData['country'] ?? null
            ]);

            // Check for geofence triggers
            $this->processGeofenceTriggers($location);

            // Clean up old location records (keep last 30 days)
            $this->cleanupOldLocations($locationData['device_id'], $locationData['enterprise_id']);

            return $location;

        } catch (\Exception $e) {
            Log::error('Error recording location: ' . $e->getMessage(), [
                'location_data' => $locationData
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'location' => null,
                'message' => 'Failed to record location'
            ];
        }
    }

    /**
     * Get current location for a device
     */
    public function getCurrentLocation($deviceId, $enterpriseId)
    {
        try {
            $location = DeviceLocation::where('device_id', $deviceId)
                ->where('enterprise_id', $enterpriseId)
                ->orderBy('location_timestamp', 'desc')
                ->first();

            if (!$location) {
                return [
                    'success' => false,
                    'error' => 'No location found for device'
                ];
            }

            // Check if location is recent (within last hour)
            $isRecent = $location->location_timestamp->gt(now()->subHour());

            return [
                'success' => true,
                'location' => $location,
                'is_recent' => $isRecent,
                'age_minutes' => $location->location_timestamp->diffInMinutes(now())
            ];

        } catch (\Exception $e) {
            Log::error('Error getting current location: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Find nearby medical facilities
     */
    public function findNearbyFacilities($latitude, $longitude, $enterpriseId, $radiusKm = 50)
    {
        try {
            $facilities = Geofence::where('enterprise_id', $enterpriseId)
                ->where('is_active', true)
                ->whereIn('fence_type', [Geofence::TYPE_HOSPITAL, Geofence::TYPE_CLINIC])
                ->get()
                ->map(function($facility) use ($latitude, $longitude) {
                    $distance = DeviceLocation::calculateDistance(
                        $latitude, 
                        $longitude, 
                        $facility->center_latitude, 
                        $facility->center_longitude
                    );
                    
                    $facility->distance_meters = $distance;
                    $facility->distance_km = round($distance / 1000, 2);
                    
                    return $facility;
                })
                ->filter(function($facility) use ($radiusKm) {
                    return $facility->distance_km <= $radiusKm;
                })
                ->sortBy('distance_meters')
                ->values();

            return [
                'success' => true,
                'facilities' => $facilities,
                'total_found' => $facilities->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error finding nearby facilities: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check geofence entry/exit
     */
    public function checkGeofenceStatus($latitude, $longitude, $enterpriseId, $deviceId = null)
    {
        try {
            $activeGeofences = Geofence::where('enterprise_id', $enterpriseId)
                ->where('is_active', true)
                ->get();

            $currentGeofences = [];
            $alerts = [];

            foreach ($activeGeofences as $geofence) {
                $isInside = $geofence->containsPoint($latitude, $longitude);
                
                if ($isInside) {
                    $currentGeofences[] = [
                        'geofence' => $geofence,
                        'distance_from_center' => DeviceLocation::calculateDistance(
                            $latitude, 
                            $longitude, 
                            $geofence->center_latitude, 
                            $geofence->center_longitude
                        ),
                        'operating_status' => $geofence->operating_status
                    ];

                    // Check for alerts
                    if ($geofence->trigger_action === Geofence::TRIGGER_ALERT) {
                        $alerts[] = [
                            'type' => 'geofence_entry',
                            'geofence' => $geofence,
                            'message' => "Entered {$geofence->name}"
                        ];
                    }
                }
            }

            return [
                'success' => true,
                'inside_geofences' => $currentGeofences,
                'alerts' => $alerts,
                'total_geofences' => count($currentGeofences)
            ];

        } catch (\Exception $e) {
            Log::error('Error checking geofence status: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get location history for a device
     */
    public function getLocationHistory($deviceId, $enterpriseId, $hours = 24, $limit = 100)
    {
        try {
            $locations = DeviceLocation::where('device_id', $deviceId)
                ->where('enterprise_id', $enterpriseId)
                ->where('location_timestamp', '>=', now()->subHours($hours))
                ->orderBy('location_timestamp', 'desc')
                ->limit($limit)
                ->get();

            // Calculate travel statistics
            $stats = $this->calculateTravelStats($locations);

            return [
                'success' => true,
                'locations' => $locations,
                'stats' => $stats,
                'total_points' => $locations->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error getting location history: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process geofence triggers
     */
    private function processGeofenceTriggers(DeviceLocation $location)
    {
        $geofences = Geofence::where('enterprise_id', $location->enterprise_id)
            ->where('is_active', true)
            ->get();

        foreach ($geofences as $geofence) {
            if ($location->isWithinGeofence($geofence)) {
                // TODO: Implement geofence trigger logic
                // - Check-in/Check-out actions
                // - Send notifications
                // - Log events
                Log::info("Device {$location->device_id} entered geofence {$geofence->name}");
            }
        }
    }

    /**
     * Clean up old location records
     */
    private function cleanupOldLocations($deviceId, $enterpriseId, $daysToKeep = 30)
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        DeviceLocation::where('device_id', $deviceId)
            ->where('enterprise_id', $enterpriseId)
            ->where('location_timestamp', '<', $cutoffDate)
            ->where('is_emergency', false) // Keep emergency locations longer
            ->delete();
    }

    /**
     * Calculate travel statistics
     */
    private function calculateTravelStats($locations)
    {
        if ($locations->count() < 2) {
            return [
                'total_distance_meters' => 0,
                'total_distance_km' => 0,
                'max_speed_mps' => 0,
                'avg_speed_mps' => 0,
                'duration_minutes' => 0
            ];
        }

        $totalDistance = 0;
        $maxSpeed = 0;
        $speedReadings = [];

        for ($i = 0; $i < $locations->count() - 1; $i++) {
            $current = $locations[$i];
            $next = $locations[$i + 1];

            // Calculate distance between consecutive points
            $distance = DeviceLocation::calculateDistance(
                $current->latitude,
                $current->longitude,
                $next->latitude,
                $next->longitude
            );
            $totalDistance += $distance;

            // Track speed readings
            if ($current->speed !== null) {
                $speedReadings[] = $current->speed;
                $maxSpeed = max($maxSpeed, $current->speed);
            }
        }

        $firstLocation = $locations->last();
        $lastLocation = $locations->first();
        $durationMinutes = $lastLocation->location_timestamp->diffInMinutes($firstLocation->location_timestamp);

        return [
            'total_distance_meters' => round($totalDistance, 2),
            'total_distance_km' => round($totalDistance / 1000, 2),
            'max_speed_mps' => round($maxSpeed, 2),
            'avg_speed_mps' => count($speedReadings) > 0 ? round(array_sum($speedReadings) / count($speedReadings), 2) : 0,
            'duration_minutes' => $durationMinutes
        ];
    }

    /**
     * Check geofence entry for a location
     */
    public function checkGeofenceEntry($location)
    {
        if (is_array($location)) {
            return [];
        }

        $triggers = [];
        $geofences = Geofence::where('enterprise_id', $location->enterprise_id)
            ->where('is_active', true)
            ->get();

        foreach ($geofences as $geofence) {
            if ($geofence->containsPoint($location->latitude, $location->longitude)) {
                $triggers[] = [
                    'geofence_id' => $geofence->id,
                    'geofence_name' => $geofence->name,
                    'facility_type' => $geofence->facility_type,
                    'trigger_actions' => $geofence->trigger_actions,
                    'is_operational' => $geofence->isOperational()
                ];
            }
        }

        return $triggers;
    }

    /**
     * Calculate travel statistics for a user
     */
    public function calculateTravelStatistics($userId, $days = 7)
    {
        $locations = DeviceLocation::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('location_timestamp', 'asc')
            ->get();

        if ($locations->count() < 2) {
            return [
                'total_locations' => $locations->count(),
                'total_distance_km' => 0,
                'average_speed_kmh' => 0,
                'max_speed_kmh' => 0,
                'travel_time_hours' => 0,
                'emergency_locations' => $locations->where('is_emergency', true)->count()
            ];
        }

        $totalDistance = 0;
        $maxSpeed = 0;
        $speedReadings = [];

        for ($i = 1; $i < $locations->count(); $i++) {
            $prev = $locations[$i - 1];
            $current = $locations[$i];

            $distance = $this->calculateDistance(
                $prev->latitude, $prev->longitude,
                $current->latitude, $current->longitude
            );

            $totalDistance += $distance;

            if ($current->speed !== null) {
                $speedKmh = $current->speed * 3.6; // Convert m/s to km/h
                $maxSpeed = max($maxSpeed, $speedKmh);
                $speedReadings[] = $speedKmh;
            }
        }

        $firstLocation = $locations->first();
        $lastLocation = $locations->last();
        $travelTimeHours = $lastLocation->location_timestamp->diffInHours($firstLocation->location_timestamp);

        return [
            'total_locations' => $locations->count(),
            'total_distance_km' => round($totalDistance / 1000, 2),
            'average_speed_kmh' => count($speedReadings) > 0 ? round(array_sum($speedReadings) / count($speedReadings), 2) : 0,
            'max_speed_kmh' => round($maxSpeed, 2),
            'travel_time_hours' => $travelTimeHours,
            'emergency_locations' => $locations->where('is_emergency', true)->count()
        ];
    }

    /**
     * Get travel analytics by period
     */
    public function getTravelAnalyticsByPeriod($userId, $period = 'weekly', $days = 30)
    {
        $analytics = [];
        $groupBy = 'DATE(created_at)';

        switch ($period) {
            case 'daily':
                $groupBy = 'DATE(created_at)';
                break;
            case 'weekly':
                $groupBy = 'YEARWEEK(created_at)';
                break;
            case 'monthly':
                $groupBy = 'YEAR(created_at), MONTH(created_at)';
                break;
        }

        $results = DeviceLocation::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw("
                {$groupBy} as period,
                COUNT(*) as location_count,
                AVG(speed) as avg_speed,
                MAX(speed) as max_speed,
                COUNT(CASE WHEN is_emergency = 1 THEN 1 END) as emergency_count
            ")
            ->groupByRaw($groupBy)
            ->orderByRaw($groupBy)
            ->get();

        return $results->map(function ($result) {
            return [
                'period' => $result->period,
                'location_count' => $result->location_count,
                'avg_speed_kmh' => $result->avg_speed ? round($result->avg_speed * 3.6, 2) : 0,
                'max_speed_kmh' => $result->max_speed ? round($result->max_speed * 3.6, 2) : 0,
                'emergency_count' => $result->emergency_count
            ];
        });
    }

    /**
     * Handle emergency location sharing
     */
    public function handleEmergencyLocation($location, $emergencyType, $message, $contactNumbers = [], $notifyServices = true)
    {
        try {
            $response = [
                'emergency_logged' => true,
                'contacts_notified' => [],
                'services_notified' => [],
                'location_shared' => true
            ];

            // Log emergency event
            Log::emergency('Emergency location shared', [
                'user_id' => is_array($location) ? null : $location->user_id,
                'location_id' => is_array($location) ? null : $location->id,
                'emergency_type' => $emergencyType,
                'message' => $message,
                'coordinates' => is_array($location) ? 
                    ['lat' => $location['latitude'] ?? null, 'lng' => $location['longitude'] ?? null] :
                    ['lat' => $location->latitude, 'lng' => $location->longitude]
            ]);

            // Notify emergency contacts (simulation)
            foreach ($contactNumbers as $contact) {
                $response['contacts_notified'][] = [
                    'contact' => $contact,
                    'status' => 'notification_sent',
                    'method' => 'sms'
                ];
            }

            // Notify emergency services if requested
            if ($notifyServices) {
                $response['services_notified'][] = [
                    'service' => 'emergency_dispatch',
                    'status' => 'notified',
                    'reference_id' => 'EMG-' . time()
                ];
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Error handling emergency location: ' . $e->getMessage());
            return [
                'emergency_logged' => false,
                'error' => $e->getMessage(),
                'contacts_notified' => [],
                'services_notified' => [],
                'location_shared' => false
            ];
        }
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }
}

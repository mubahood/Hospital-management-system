<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceLocation;
use App\Models\Geofence;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->middleware('auth:api');
        $this->locationService = $locationService;
    }

    /**
     * Record GPS location for the authenticated user's device
     * 
     * POST /api/location/record
     */
    public function recordLocation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'altitude' => 'nullable|numeric',
                'accuracy' => 'nullable|numeric|min:0',
                'heading' => 'nullable|numeric|between:0,360',
                'speed' => 'nullable|numeric|min:0',
                'device_id' => 'required|string|max:255',
                'is_emergency' => 'boolean',
                'metadata' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $locationData = $validator->validated();
            $locationData['user_id'] = $user->id;
            $locationData['enterprise_id'] = $user->enterprise_id;

            // Record location using service
            $location = $this->locationService->recordLocation($locationData);

            // Check for geofence triggers
            $triggers = $this->locationService->checkGeofenceEntry($location);

            return response()->json([
                'success' => true,
                'message' => 'Location recorded successfully',
                'data' => [
                    'location' => $location,
                    'geofence_triggers' => $triggers
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Location recording failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to record location'
            ], 500);
        }
    }

    /**
     * Find nearby healthcare facilities
     * 
     * GET /api/location/nearby-facilities
     */
    public function findNearbyFacilities(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|numeric|min:0.1|max:50', // Max 50km radius
                'facility_type' => 'nullable|string|in:hospital,clinic,pharmacy,emergency',
                'emergency_only' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $radius = $request->get('radius', 10); // Default 10km radius
            
            $facilities = $this->locationService->findNearbyFacilities(
                $request->latitude,
                $request->longitude,
                $radius,
                $user->enterprise_id,
                [
                    'facility_type' => $request->facility_type,
                    'emergency_only' => $request->boolean('emergency_only')
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Nearby facilities retrieved successfully',
                'data' => [
                    'facilities' => $facilities,
                    'search_center' => [
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                        'radius_km' => $radius
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Nearby facilities search failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to find nearby facilities'
            ], 500);
        }
    }

    /**
     * Share emergency location with contacts and emergency services
     * 
     * POST /api/location/emergency-share
     */
    public function shareEmergencyLocation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'emergency_type' => 'required|string|in:medical,accident,security,fire,other',
                'message' => 'nullable|string|max:500',
                'contact_numbers' => 'nullable|array',
                'contact_numbers.*' => 'string|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            // Record emergency location
            $locationData = [
                'user_id' => $user->id,
                'enterprise_id' => $user->enterprise_id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_emergency' => true,
                'device_id' => $request->get('device_id', 'emergency_request'),
                'metadata' => [
                    'emergency_type' => $request->emergency_type,
                    'emergency_message' => $request->message,
                    'timestamp' => now()->toISOString()
                ]
            ];

            $location = $this->locationService->recordLocation($locationData);

            // Handle emergency sharing
            $emergencyResponse = $this->locationService->handleEmergencyLocation(
                $location,
                $request->emergency_type,
                $request->message,
                $request->contact_numbers ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Emergency location shared successfully',
                'data' => [
                    'location' => $location,
                    'emergency_response' => $emergencyResponse,
                    'emergency_id' => $location->id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Emergency location sharing failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to share emergency location'
            ], 500);
        }
    }

    /**
     * Get location history for the authenticated user
     * 
     * GET /api/location/history
     */
    public function getLocationHistory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'days' => 'nullable|integer|min:1|max:90', // Max 90 days
                'limit' => 'nullable|integer|min:1|max:1000',
                'emergency_only' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $days = $request->get('days', 7); // Default 7 days
            $limit = $request->get('limit', 100);
            $emergencyOnly = $request->boolean('emergency_only');

            $query = DeviceLocation::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subDays($days))
                ->orderBy('created_at', 'desc')
                ->limit($limit);

            if ($emergencyOnly) {
                $query->where('is_emergency', true);
            }

            $locations = $query->get();

            // Calculate travel statistics
            $travelStats = $this->locationService->calculateTravelStatistics($user->id, $days);

            return response()->json([
                'success' => true,
                'message' => 'Location history retrieved successfully',
                'data' => [
                    'locations' => $locations,
                    'travel_statistics' => $travelStats,
                    'period' => [
                        'days' => $days,
                        'from' => now()->subDays($days)->toDateString(),
                        'to' => now()->toDateString()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Location history retrieval failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve location history'
            ], 500);
        }
    }

    /**
     * Get available geofences for the enterprise
     * 
     * GET /api/location/geofences
     */
    public function getGeofences(Request $request)
    {
        try {
            $user = Auth::user();
            
            $query = Geofence::where('enterprise_id', $user->enterprise_id)
                ->where('is_active', true)
                ->orderBy('name');

            // Filter by facility type if specified
            if ($request->has('facility_type')) {
                $query->where('facility_type', $request->facility_type);
            }

            $geofences = $query->get()->map(function ($geofence) {
                return [
                    'id' => $geofence->id,
                    'name' => $geofence->name,
                    'facility_type' => $geofence->facility_type,
                    'center_latitude' => $geofence->center_latitude,
                    'center_longitude' => $geofence->center_longitude,
                    'radius_meters' => $geofence->radius_meters,
                    'services_available' => $geofence->services_available,
                    'is_operational' => $geofence->isOperational(),
                    'operating_hours' => $geofence->operating_hours
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Geofences retrieved successfully',
                'data' => [
                    'geofences' => $geofences,
                    'total_count' => $geofences->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Geofences retrieval failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve geofences'
            ], 500);
        }
    }

    /**
     * Check if a location is within any geofence
     * 
     * POST /api/location/check-geofence
     */
    public function checkGeofence(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            $geofences = Geofence::where('enterprise_id', $user->enterprise_id)
                ->where('is_active', true)
                ->get();

            $withinGeofences = [];
            foreach ($geofences as $geofence) {
                if ($geofence->containsPoint($request->latitude, $request->longitude)) {
                    $withinGeofences[] = [
                        'id' => $geofence->id,
                        'name' => $geofence->name,
                        'facility_type' => $geofence->facility_type,
                        'is_operational' => $geofence->isOperational(),
                        'services_available' => $geofence->services_available,
                        'trigger_actions' => $geofence->trigger_actions
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Geofence check completed',
                'data' => [
                    'is_within_geofence' => !empty($withinGeofences),
                    'geofences' => $withinGeofences,
                    'checked_location' => [
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Geofence check failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check geofence'
            ], 500);
        }
    }

    /**
     * Get travel analytics for the authenticated user
     * 
     * GET /api/location/travel-analytics
     */
    public function getTravelAnalytics(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'period' => 'nullable|string|in:daily,weekly,monthly',
                'days' => 'nullable|integer|min:1|max:365'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $period = $request->get('period', 'weekly');
            $days = $request->get('days', 30);

            $analytics = $this->locationService->calculateTravelStatistics($user->id, $days);
            
            // Add period-specific analytics
            $periodAnalytics = $this->locationService->getTravelAnalyticsByPeriod($user->id, $period, $days);

            return response()->json([
                'success' => true,
                'message' => 'Travel analytics retrieved successfully',
                'data' => [
                    'general_statistics' => $analytics,
                    'period_analytics' => $periodAnalytics,
                    'analysis_period' => [
                        'type' => $period,
                        'days' => $days,
                        'from' => now()->subDays($days)->toDateString(),
                        'to' => now()->toDateString()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Travel analytics retrieval failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve travel analytics'
            ], 500);
        }
    }
}

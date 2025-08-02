<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceLocation extends Model
{
    use HasFactory, EnterpriseScopeTrait, StandardBootTrait;

    protected $fillable = [
        'user_id',
        'enterprise_id',
        'device_id',
        'latitude',
        'longitude',
        'altitude',
        'accuracy',
        'heading',
        'speed',
        'location_type',
        'location_timestamp',
        'additional_data',
        'is_emergency',
        'address',
        'city',
        'country'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'altitude' => 'decimal:2',
        'accuracy' => 'decimal:2',
        'heading' => 'decimal:2',
        'speed' => 'decimal:2',
        'location_timestamp' => 'datetime',
        'additional_data' => 'array',
        'is_emergency' => 'boolean'
    ];

    // Location types
    const TYPE_GPS = 'gps';
    const TYPE_NETWORK = 'network';
    const TYPE_PASSIVE = 'passive';
    const TYPE_FUSED = 'fused';

    /**
     * Get the user that owns the device location
     */
    public function user()
    {
        return $this->belongsTo(Administrator::class, 'user_id');
    }

    /**
     * Get the enterprise this location belongs to
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * Scope for emergency locations
     */
    public function scopeEmergency($query)
    {
        return $query->where('is_emergency', true);
    }

    /**
     * Scope for recent locations
     */
    public function scopeRecent($query, $minutes = 60)
    {
        return $query->where('location_timestamp', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope for locations by device
     */
    public function scopeByDevice($query, $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    /**
     * Scope for locations within radius
     */
    public function scopeWithinRadius($query, $latitude, $longitude, $radiusKm)
    {
        $earthRadius = 6371; // Earth's radius in kilometers
        
        return $query->selectRaw('*,
            ( ? * acos( cos( radians(?) ) *
            cos( radians( latitude ) ) *
            cos( radians( longitude ) - radians(?) ) +
            sin( radians(?) ) *
            sin( radians( latitude ) ) ) ) AS distance', 
            [$earthRadius, $latitude, $longitude, $latitude])
            ->having('distance', '<', $radiusKm);
    }

    /**
     * Get the latest location for a device
     */
    public static function getLatestForDevice($deviceId, $enterpriseId = null)
    {
        $query = self::where('device_id', $deviceId);
        
        if ($enterpriseId) {
            $query->where('enterprise_id', $enterpriseId);
        }
        
        return $query->orderBy('location_timestamp', 'desc')->first();
    }

    /**
     * Calculate distance between two points in meters
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth's radius in meters
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    /**
     * Check if location is within a geofence
     */
    public function isWithinGeofence(Geofence $geofence)
    {
        $distance = self::calculateDistance(
            $this->latitude,
            $this->longitude,
            $geofence->center_latitude,
            $geofence->center_longitude
        );
        
        return $distance <= $geofence->radius;
    }

    /**
     * Get nearby geofences
     */
    public function getNearbyGeofences($maxDistance = 5000) // 5km default
    {
        return Geofence::where('enterprise_id', $this->enterprise_id)
            ->where('is_active', true)
            ->get()
            ->filter(function($geofence) use ($maxDistance) {
                $distance = self::calculateDistance(
                    $this->latitude,
                    $this->longitude,
                    $geofence->center_latitude,
                    $geofence->center_longitude
                );
                return $distance <= $maxDistance;
            });
    }

    /**
     * Get formatted address
     */
    public function getFormattedAddressAttribute()
    {
        $parts = array_filter([$this->address, $this->city, $this->country]);
        return implode(', ', $parts);
    }

    /**
     * Get location accuracy description
     */
    public function getAccuracyDescriptionAttribute()
    {
        if (!$this->accuracy) return 'Unknown';
        
        if ($this->accuracy <= 5) return 'Excellent';
        if ($this->accuracy <= 10) return 'Good';
        if ($this->accuracy <= 50) return 'Fair';
        return 'Poor';
    }
}

<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Geofence extends Model
{
    use HasFactory, EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'name',
        'description',
        'center_latitude',
        'center_longitude',
        'radius',
        'fence_type',
        'trigger_action',
        'is_active',
        'trigger_settings',
        'address',
        'contact_phone',
        'contact_email',
        'operating_hours',
        'services_available'
    ];

    protected $casts = [
        'center_latitude' => 'decimal:8',
        'center_longitude' => 'decimal:8',
        'radius' => 'decimal:2',
        'is_active' => 'boolean',
        'trigger_settings' => 'array',
        'operating_hours' => 'array',
        'services_available' => 'array'
    ];

    // Fence types
    const TYPE_HOSPITAL = 'hospital';
    const TYPE_CLINIC = 'clinic';
    const TYPE_EMERGENCY_ZONE = 'emergency_zone';
    const TYPE_RESTRICTED_AREA = 'restricted_area';
    const TYPE_PARKING = 'parking';

    // Trigger actions
    const TRIGGER_CHECK_IN = 'check_in';
    const TRIGGER_CHECK_OUT = 'check_out';
    const TRIGGER_BOTH = 'both';
    const TRIGGER_ALERT = 'alert';

    /**
     * Get the enterprise this geofence belongs to
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * Get device locations within this geofence
     */
    public function deviceLocations()
    {
        return $this->hasMany(DeviceLocation::class);
    }

    /**
     * Scope for active geofences
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for geofences by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('fence_type', $type);
    }

    /**
     * Scope for geofences within radius of a point
     */
    public function scopeNearLocation($query, $latitude, $longitude, $maxDistance = 50000) // 50km default
    {
        $earthRadius = 6371000; // Earth's radius in meters
        
        return $query->selectRaw('*,
            ( ? * acos( cos( radians(?) ) *
            cos( radians( center_latitude ) ) *
            cos( radians( center_longitude ) - radians(?) ) +
            sin( radians(?) ) *
            sin( radians( center_latitude ) ) ) ) AS distance', 
            [$earthRadius, $latitude, $longitude, $latitude])
            ->having('distance', '<', $maxDistance);
    }

    /**
     * Check if a point is within this geofence
     */
    public function containsPoint($latitude, $longitude)
    {
        $distance = DeviceLocation::calculateDistance(
            $latitude,
            $longitude,
            $this->center_latitude,
            $this->center_longitude
        );
        
        return $distance <= $this->radius;
    }

    /**
     * Get current device locations within this geofence
     */
    public function getCurrentDevicesInside($minutes = 10)
    {
        return DeviceLocation::where('enterprise_id', $this->enterprise_id)
            ->recent($minutes)
            ->get()
            ->filter(function($location) {
                return $this->containsPoint($location->latitude, $location->longitude);
            });
    }

    /**
     * Get operating status based on current time
     */
    public function getOperatingStatusAttribute()
    {
        if (!$this->operating_hours) {
            return 'always_open';
        }

        $currentDay = strtolower(now()->format('l')); // Monday, Tuesday, etc.
        $currentTime = now()->format('H:i');

        if (!isset($this->operating_hours[$currentDay])) {
            return 'closed';
        }

        $hours = $this->operating_hours[$currentDay];
        
        if ($hours['closed']) {
            return 'closed';
        }

        if ($currentTime >= $hours['open'] && $currentTime <= $hours['close']) {
            return 'open';
        }

        return 'closed';
    }

    /**
     * Get distance from a point
     */
    public function getDistanceFrom($latitude, $longitude)
    {
        return DeviceLocation::calculateDistance(
            $latitude,
            $longitude,
            $this->center_latitude,
            $this->center_longitude
        );
    }

    /**
     * Get available services as a formatted string
     */
    public function getFormattedServicesAttribute()
    {
        if (!$this->services_available) {
            return 'General Services';
        }

        return implode(', ', $this->services_available);
    }

    /**
     * Get contact information as formatted string
     */
    public function getContactInfoAttribute()
    {
        $contact = [];
        
        if ($this->contact_phone) {
            $contact[] = 'Phone: ' . $this->contact_phone;
        }
        
        if ($this->contact_email) {
            $contact[] = 'Email: ' . $this->contact_email;
        }
        
        return implode(' | ', $contact);
    }
}

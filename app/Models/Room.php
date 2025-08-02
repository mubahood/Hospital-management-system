<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory, EnterpriseScopeTrait, StandardBootTrait;

    protected $fillable = [
        'enterprise_id',
        'room_number',
        'name',
        'description',
        'room_type',
        'capacity',
        'equipment',
        'features',
        'floor',
        'building',
        'extension',
        'is_active',
        'is_available',
        'unavailable_reason',
        'unavailable_until',
        'last_cleaned_at',
        'last_maintenance_at',
        'next_maintenance_due'
    ];

    protected $casts = [
        'equipment' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'unavailable_until' => 'datetime',
        'last_cleaned_at' => 'datetime',
        'last_maintenance_at' => 'datetime',
        'next_maintenance_due' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function enterprise()
    {
        return $this->belongsTo(Company::class, 'enterprise_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
                    ->where(function ($q) {
                        $q->whereNull('unavailable_until')
                          ->orWhere('unavailable_until', '<=', now());
                    });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('room_type', $type);
    }

    /**
     * Business Logic Methods
     */
    
    /**
     * Check if room is available at a specific time
     */
    public function isAvailableAt($startTime, $endTime): bool
    {
        if (!$this->is_active || !$this->is_available) {
            return false;
        }

        if ($this->unavailable_until && $this->unavailable_until > $startTime) {
            return false;
        }

        // Check for conflicting appointments
        $conflicts = $this->appointments()
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('appointment_date', [$startTime, $endTime])
                  ->orWhereBetween('appointment_end_date', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('appointment_date', '<=', $startTime)
                         ->where('appointment_end_date', '>=', $endTime);
                  });
            })
            ->exists();

        return !$conflicts;
    }

    /**
     * Mark room as unavailable
     */
    public function markUnavailable($reason, $until = null): bool
    {
        return $this->update([
            'is_available' => false,
            'unavailable_reason' => $reason,
            'unavailable_until' => $until
        ]);
    }

    /**
     * Mark room as available
     */
    public function markAvailable(): bool
    {
        return $this->update([
            'is_available' => true,
            'unavailable_reason' => null,
            'unavailable_until' => null
        ]);
    }

    /**
     * Get room types
     */
    public static function getRoomTypes(): array
    {
        return [
            'consultation' => 'Consultation Room',
            'surgery' => 'Surgery Room',
            'procedure' => 'Procedure Room',
            'laboratory' => 'Laboratory',
            'imaging' => 'Imaging Room',
            'therapy' => 'Therapy Room',
            'emergency' => 'Emergency Room',
            'ward' => 'Ward',
            'icu' => 'ICU',
            'pharmacy' => 'Pharmacy'
        ];
    }

    /**
     * Get available rooms for a specific time slot and type
     */
    public static function getAvailableRooms($startTime, $endTime, $roomType = null, $enterpriseId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::active()->available();

        if ($roomType) {
            $query->byType($roomType);
        }

        if ($enterpriseId) {
            $query->where('enterprise_id', $enterpriseId);
        }

        return $query->get()->filter(function ($room) use ($startTime, $endTime) {
            return $room->isAvailableAt($startTime, $endTime);
        });
    }
}

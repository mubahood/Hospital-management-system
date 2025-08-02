<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    use HasFactory, EnterpriseScopeTrait, StandardBootTrait;

    protected $fillable = [
        'enterprise_id',
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration_minutes',
        'break_duration_minutes',
        'is_active',
        'effective_from',
        'effective_to',
        'max_patients_per_slot',
        'buffer_time_minutes',
        'break_times',
        'special_dates'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'break_times' => 'array',
        'special_dates' => 'array'
    ];

    /**
     * Relationships
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function enterprise()
    {
        return $this->belongsTo(Company::class, 'enterprise_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForDay($query, $dayOfWeek)
    {
        return $query->where('day_of_week', strtolower($dayOfWeek));
    }

    public function scopeEffectiveOn($query, $date)
    {
        return $query->where('effective_from', '<=', $date)
                    ->where(function ($q) use ($date) {
                        $q->whereNull('effective_to')
                          ->orWhere('effective_to', '>=', $date);
                    });
    }

    /**
     * Business Logic Methods
     */
    
    /**
     * Get schedule for a specific doctor and date
     */
    public static function getScheduleForDate($doctorId, $date)
    {
        $dayOfWeek = Carbon::parse($date)->format('l');
        
        return static::forDoctor($doctorId)
            ->forDay($dayOfWeek)
            ->effectiveOn($date)
            ->active()
            ->get();
    }

    /**
     * Check if doctor is available at specific time
     */
    public function isAvailableAt($time): bool
    {
        $timeOnly = Carbon::parse($time)->format('H:i:s');
        $startTime = Carbon::parse($this->start_time)->format('H:i:s');
        $endTime = Carbon::parse($this->end_time)->format('H:i:s');

        if ($timeOnly < $startTime || $timeOnly > $endTime) {
            return false;
        }

        // Check break times
        if ($this->break_times) {
            foreach ($this->break_times as $breakTime) {
                $breakStart = Carbon::parse($breakTime['start_time'])->format('H:i:s');
                $breakEnd = Carbon::parse($breakTime['end_time'])->format('H:i:s');
                
                if ($timeOnly >= $breakStart && $timeOnly <= $breakEnd) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Generate time slots for the schedule
     */
    public function generateTimeSlots($date = null): array
    {
        $slots = [];
        $startTime = Carbon::parse(($date ?? today()) . ' ' . $this->start_time);
        $endTime = Carbon::parse(($date ?? today()) . ' ' . $this->end_time);
        
        while ($startTime->lt($endTime)) {
            $slotEnd = $startTime->copy()->addMinutes($this->slot_duration_minutes);
            
            if ($slotEnd->lte($endTime)) {
                $slots[] = [
                    'start_time' => $startTime->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'datetime' => $startTime->toDateTimeString(),
                    'is_available' => $this->isAvailableAt($startTime->format('H:i:s'))
                ];
            }
            
            $startTime->addMinutes($this->slot_duration_minutes + $this->buffer_time_minutes);
        }

        return $slots;
    }

    /**
     * Get doctor's weekly schedule
     */
    public static function getWeeklySchedule($doctorId, $weekStartDate = null): array
    {
        $weekStart = $weekStartDate ? Carbon::parse($weekStartDate) : Carbon::now()->startOfWeek();
        $schedule = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $dayOfWeek = $date->format('l');
            
            $daySchedule = static::getScheduleForDate($doctorId, $date->toDateString());
            
            $schedule[$dayOfWeek] = [
                'date' => $date->toDateString(),
                'day' => $dayOfWeek,
                'schedules' => $daySchedule,
                'is_available' => $daySchedule->isNotEmpty()
            ];
        }

        return $schedule;
    }

    /**
     * Check for schedule conflicts
     */
    public function hasConflicts(): bool
    {
        return static::where('doctor_id', $this->doctor_id)
            ->where('id', '!=', $this->id)
            ->where('day_of_week', $this->day_of_week)
            ->where('is_active', true)
            ->where('effective_from', '<=', $this->effective_to ?? '2099-12-31')
            ->where(function ($q) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $this->effective_from);
            })
            ->where(function ($q) {
                // Check for time overlap
                $q->where(function ($q2) {
                    $q2->where('start_time', '<', $this->end_time)
                       ->where('end_time', '>', $this->start_time);
                });
            })
            ->exists();
    }

    /**
     * Get days of week options
     */
    public static function getDaysOfWeek(): array
    {
        return [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday'
        ];
    }

    /**
     * Create default schedule for a doctor
     */
    public static function createDefaultSchedule($doctorId, $enterpriseId): array
    {
        $schedules = [];
        $defaultDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        foreach ($defaultDays as $day) {
            $schedule = static::create([
                'enterprise_id' => $enterpriseId,
                'doctor_id' => $doctorId,
                'day_of_week' => $day,
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'slot_duration_minutes' => 30,
                'break_duration_minutes' => 0,
                'buffer_time_minutes' => 5,
                'max_patients_per_slot' => 1,
                'effective_from' => today(),
                'is_active' => true,
                'break_times' => [
                    [
                        'start_time' => '12:00:00',
                        'end_time' => '13:00:00',
                        'title' => 'Lunch Break'
                    ]
                ]
            ]);

            $schedules[] = $schedule;
        }

        return $schedules;
    }

    /**
     * Bulk update schedules
     */
    public static function bulkUpdate($doctorId, $schedules): bool
    {
        try {
            \DB::transaction(function () use ($doctorId, $schedules) {
                foreach ($schedules as $dayOfWeek => $scheduleData) {
                    static::updateOrCreate(
                        [
                            'doctor_id' => $doctorId,
                            'day_of_week' => $dayOfWeek,
                            'effective_from' => $scheduleData['effective_from'] ?? today()
                        ],
                        $scheduleData
                    );
                }
            });

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

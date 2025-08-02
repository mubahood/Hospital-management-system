<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Appointment extends Model
{
    use HasFactory, EnterpriseScopeTrait, StandardBootTrait;

    protected $fillable = [
        'enterprise_id',
        'appointment_number',
        'patient_id',
        'doctor_id',
        'department_id',
        'appointment_date',
        'appointment_end_date',
        'duration_minutes',
        'appointment_type',
        'priority',
        'status',
        'reason',
        'notes',
        'preparation_instructions',
        'services_requested',
        'room_id',
        'equipment_ids',
        'is_recurring',
        'recurrence_type',
        'recurrence_interval',
        'recurrence_end_date',
        'parent_appointment_id',
        'sms_reminder_sent',
        'email_reminder_sent',
        'reminder_sent_at',
        'confirmation_required',
        'confirmed_at',
        'confirmed_by',
        'checked_in_at',
        'started_at',
        'completed_at',
        'created_by',
        'updated_by',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by'
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'appointment_end_date' => 'datetime',
        'services_requested' => 'array',
        'equipment_ids' => 'array',
        'is_recurring' => 'boolean',
        'sms_reminder_sent' => 'boolean',
        'email_reminder_sent' => 'boolean',
        'confirmation_required' => 'boolean',
        'reminder_sent_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'recurrence_end_date' => 'date'
    ];

    /**
     * Boot method to generate appointment number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->appointment_number)) {
                $model->appointment_number = static::generateAppointmentNumber($model->enterprise_id);
            }

            // Set end date if not provided
            if (empty($model->appointment_end_date) && !empty($model->appointment_date)) {
                $model->appointment_end_date = Carbon::parse($model->appointment_date)
                    ->addMinutes($model->duration_minutes ?? 30);
            }
        });

        static::updating(function ($model) {
            // Update end date if appointment date or duration changed
            if ($model->isDirty(['appointment_date', 'duration_minutes'])) {
                $model->appointment_end_date = Carbon::parse($model->appointment_date)
                    ->addMinutes($model->duration_minutes ?? 30);
            }
        });
    }

    /**
     * Generate unique appointment number
     */
    public static function generateAppointmentNumber($enterpriseId): string
    {
        $date = now()->format('Y-m-d');
        $prefix = 'APT-' . now()->format('Ymd') . '-';
        
        $lastAppointment = static::where('enterprise_id', $enterpriseId)
            ->where('appointment_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastAppointment) {
            $lastNumber = (int) str_replace($prefix, '', $lastAppointment->appointment_number);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function enterprise()
    {
        return $this->belongsTo(Company::class, 'enterprise_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function parentAppointment()
    {
        return $this->belongsTo(Appointment::class, 'parent_appointment_id');
    }

    public function childAppointments()
    {
        return $this->hasMany(Appointment::class, 'parent_appointment_id');
    }

    public function consultation()
    {
        return $this->hasOne(Consultation::class, 'appointment_id');
    }

    /**
     * Scopes
     */
    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', now())
                    ->whereIn('status', ['scheduled', 'confirmed']);
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('appointment_type', $type);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('appointment_date', [$startDate, $endDate]);
    }

    /**
     * Business Logic Methods
     */
    
    /**
     * Check if appointment can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed']) && 
               $this->appointment_date > now();
    }

    /**
     * Check if appointment can be rescheduled
     */
    public function canBeRescheduled(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed', 'cancelled']) && 
               $this->appointment_date > now();
    }

    /**
     * Check if appointment is overdue
     */
    public function isOverdue(): bool
    {
        return $this->appointment_date < now() && 
               in_array($this->status, ['scheduled', 'confirmed']);
    }

    /**
     * Get appointment duration in minutes
     */
    public function getDurationAttribute(): int
    {
        if ($this->appointment_end_date && $this->appointment_date) {
            return $this->appointment_date->diffInMinutes($this->appointment_end_date);
        }
        return $this->duration_minutes ?? 30;
    }

    /**
     * Check for conflicts with other appointments
     */
    public function hasConflicts(): bool
    {
        $query = static::where('doctor_id', $this->doctor_id)
            ->where('id', '!=', $this->id)
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->where(function ($q) {
                // Check for time overlap
                $q->whereBetween('appointment_date', [$this->appointment_date, $this->appointment_end_date])
                  ->orWhereBetween('appointment_end_date', [$this->appointment_date, $this->appointment_end_date])
                  ->orWhere(function ($q2) {
                      $q2->where('appointment_date', '<=', $this->appointment_date)
                         ->where('appointment_end_date', '>=', $this->appointment_end_date);
                  });
            });

        return $query->exists();
    }

    /**
     * Get available time slots for a doctor on a specific date
     */
    public static function getAvailableSlots($doctorId, $date, $duration = 30): array
    {
        $schedule = DoctorSchedule::getScheduleForDate($doctorId, $date);
        if (!$schedule) {
            return [];
        }

        $dayOfWeek = Carbon::parse($date)->format('l');
        $daySchedule = $schedule->where('day_of_week', strtolower($dayOfWeek))->first();
        
        if (!$daySchedule) {
            return [];
        }

        // Get existing appointments for the date
        $existingAppointments = static::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->orderBy('appointment_date')
            ->get();

        // Generate time slots
        $slots = [];
        $startTime = Carbon::parse($date . ' ' . $daySchedule->start_time);
        $endTime = Carbon::parse($date . ' ' . $daySchedule->end_time);
        $slotDuration = $daySchedule->slot_duration_minutes;
        $bufferTime = $daySchedule->buffer_time_minutes;

        while ($startTime->addMinutes($slotDuration)->lte($endTime)) {
            $slotEnd = $startTime->copy()->addMinutes($duration);
            
            // Check if slot conflicts with existing appointments
            $hasConflict = $existingAppointments->contains(function ($appointment) use ($startTime, $slotEnd) {
                return $startTime->lt($appointment->appointment_end_date) && 
                       $slotEnd->gt($appointment->appointment_date);
            });

            if (!$hasConflict) {
                $slots[] = [
                    'start_time' => $startTime->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'datetime' => $startTime->toDateTimeString(),
                    'available' => true
                ];
            }

            $startTime->addMinutes($bufferTime);
        }

        return $slots;
    }

    /**
     * Cancel appointment
     */
    public function cancel($reason = null, $cancelledBy = null): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy ?? auth()->id()
        ]);

        // Cancel child appointments if this is a recurring appointment
        if ($this->is_recurring) {
            $this->childAppointments()
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => 'Parent appointment cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => $cancelledBy ?? auth()->id()
                ]);
        }

        return true;
    }

    /**
     * Confirm appointment
     */
    public function confirm($confirmedBy = null): bool
    {
        if ($this->status !== 'scheduled') {
            return false;
        }

        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $confirmedBy ?? auth()->id()
        ]);

        return true;
    }

    /**
     * Check in patient
     */
    public function checkIn(): bool
    {
        if ($this->status !== 'confirmed') {
            return false;
        }

        $this->update([
            'status' => 'in_progress',
            'checked_in_at' => now(),
            'started_at' => now()
        ]);

        return true;
    }

    /**
     * Complete appointment
     */
    public function complete(): bool
    {
        if ($this->status !== 'in_progress') {
            return false;
        }

        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        return true;
    }

    /**
     * Mark as no show
     */
    public function markAsNoShow(): bool
    {
        if (!in_array($this->status, ['scheduled', 'confirmed'])) {
            return false;
        }

        $this->update([
            'status' => 'no_show'
        ]);

        return true;
    }

    /**
     * Reschedule appointment
     */
    public function reschedule($newDate, $newDuration = null): bool
    {
        if (!$this->canBeRescheduled()) {
            return false;
        }

        $duration = $newDuration ?? $this->duration_minutes;
        $newEndDate = Carbon::parse($newDate)->addMinutes($duration);

        // Create temporary appointment to check conflicts
        $tempAppointment = new static([
            'doctor_id' => $this->doctor_id,
            'appointment_date' => $newDate,
            'appointment_end_date' => $newEndDate,
            'duration_minutes' => $duration
        ]);
        $tempAppointment->id = $this->id; // To exclude self from conflict check

        if ($tempAppointment->hasConflicts()) {
            return false;
        }

        $this->update([
            'appointment_date' => $newDate,
            'appointment_end_date' => $newEndDate,
            'duration_minutes' => $duration,
            'status' => 'rescheduled'
        ]);

        return true;
    }

    /**
     * Get appointment types
     */
    public static function getAppointmentTypes(): array
    {
        return [
            'consultation' => 'Consultation',
            'follow_up' => 'Follow-up',
            'surgery' => 'Surgery',
            'procedure' => 'Procedure',
            'lab_test' => 'Laboratory Test',
            'imaging' => 'Medical Imaging',
            'therapy' => 'Therapy',
            'vaccination' => 'Vaccination',
            'emergency' => 'Emergency'
        ];
    }

    /**
     * Get priority levels
     */
    public static function getPriorityLevels(): array
    {
        return [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];
    }

    /**
     * Get status options
     */
    public static function getStatusOptions(): array
    {
        return [
            'scheduled' => 'Scheduled',
            'confirmed' => 'Confirmed',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            'rescheduled' => 'Rescheduled'
        ];
    }

    /**
     * Create recurring appointments
     */
    public function createRecurringAppointments(): array
    {
        if (!$this->is_recurring || !$this->recurrence_type) {
            return [];
        }

        $appointments = [];
        $currentDate = Carbon::parse($this->appointment_date);
        $endDate = $this->recurrence_end_date ? Carbon::parse($this->recurrence_end_date) : $currentDate->copy()->addYear();
        $interval = $this->recurrence_interval ?? 1;

        while ($currentDate->lte($endDate)) {
            // Calculate next occurrence
            switch ($this->recurrence_type) {
                case 'daily':
                    $currentDate->addDays($interval);
                    break;
                case 'weekly':
                    $currentDate->addWeeks($interval);
                    break;
                case 'monthly':
                    $currentDate->addMonths($interval);
                    break;
                case 'yearly':
                    $currentDate->addYears($interval);
                    break;
            }

            if ($currentDate->lte($endDate)) {
                $newAppointment = static::create([
                    'enterprise_id' => $this->enterprise_id,
                    'patient_id' => $this->patient_id,
                    'doctor_id' => $this->doctor_id,
                    'department_id' => $this->department_id,
                    'appointment_date' => $currentDate->toDateTimeString(),
                    'duration_minutes' => $this->duration_minutes,
                    'appointment_type' => $this->appointment_type,
                    'priority' => $this->priority,
                    'reason' => $this->reason,
                    'notes' => $this->notes,
                    'room_id' => $this->room_id,
                    'parent_appointment_id' => $this->id,
                    'created_by' => $this->created_by
                ]);

                $appointments[] = $newAppointment;
            }
        }

        return $appointments;
    }
}

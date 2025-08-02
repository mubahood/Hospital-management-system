<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Patient extends Model
{
    use HasFactory, EnterpriseScopeTrait, SoftDeletes, StandardBootTrait;

    /**
     * The table associated with the model.
     */
    protected $table = 'patients';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'enterprise_id',
        'administrator_id',
        'patient_number',
        'first_name',
        'last_name',
        'phone_number_1',
        'phone_number_2',
        'email',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'country',
        'emergency_contact_name',
        'emergency_contact_phone',
        'blood_group',
        'allergies',
        'medical_history',
        'insurance_provider',
        'insurance_number',
        'occupation',
        'marital_status',
        'nationality',
        'profile_photo',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'allergies' => 'array',
        'medical_history' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'full_name',
        'age',
        'formatted_phone',
        'profile_photo_url'
    ];

    /**
     * Boot the model with standardized event handling.
     */
    protected static function boot(): void
    {
        parent::boot();
    }

    /**
     * Custom logic before creating a patient.
     */
    protected function processCustomBeforeCreate(): void
    {
        $this->generatePatientNumberIfNeeded();
    }

    /**
     * Generate patient number if not provided.
     */
    private function generatePatientNumberIfNeeded(): void
    {
        if (empty($this->patient_number)) {
            $this->patient_number = self::generatePatientNumber($this->enterprise_id);
        }
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Get the administrator that created this patient.
     */
    public function administrator()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    /**
     * Get the enterprise this patient belongs to.
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }

    /**
     * Get the consultations for this patient.
     */
    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'patient_id');
    }

    /**
     * Get the medical services for this patient.
     */
    public function medicalServices()
    {
        return $this->hasMany(MedicalService::class, 'patient_id');
    }

    /**
     * Get the patient records for this patient.
     */
    public function patientRecords()
    {
        return $this->hasMany(PatientRecord::class, 'patient_id');
    }

    /**
     * Get the treatment records for this patient.
     */
    public function treatmentRecords()
    {
        return $this->hasMany(TreatmentRecord::class, 'patient_id');
    }

    /**
     * Get the billing items for this patient.
     */
    public function billingItems()
    {
        return $this->hasMany(BillingItem::class, 'patient_id');
    }

    /**
     * Get the payment records for this patient.
     */
    public function paymentRecords()
    {
        return $this->hasMany(PaymentRecord::class, 'patient_id');
    }

    // ============================================
    // ACCESSORS & MUTATORS
    // ============================================

    /**
     * Get the patient's full name.
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get the patient's age.
     */
    public function getAgeAttribute()
    {
        if (!$this->date_of_birth) {
            return null;
        }
        return Carbon::parse($this->date_of_birth)->age;
    }

    /**
     * Get formatted phone number.
     */
    public function getFormattedPhoneAttribute()
    {
        return $this->phone_number_1 ?: $this->phone_number_2;
    }

    /**
     * Get profile photo URL.
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }
        return asset('images/default-avatar.png');
    }

    /**
     * Set the first name attribute.
     */
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucfirst(strtolower($value));
    }

    /**
     * Set the last name attribute.
     */
    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = ucfirst(strtolower($value));
    }

    /**
     * Set the email attribute.
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope a query to only include active patients.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include patients with consultations.
     */
    public function scopeWithConsultations($query)
    {
        return $query->has('consultations');
    }

    /**
     * Scope a query to search patients by name or phone.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('phone_number_1', 'like', "%{$search}%")
              ->orWhere('phone_number_2', 'like', "%{$search}%")
              ->orWhere('patient_number', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by gender.
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope a query to filter by age range.
     */
    public function scopeByAgeRange($query, $minAge, $maxAge)
    {
        $maxDate = Carbon::now()->subYears($minAge);
        $minDate = Carbon::now()->subYears($maxAge);
        
        return $query->whereBetween('date_of_birth', [$minDate, $maxDate]);
    }

    // ============================================
    // STATIC METHODS
    // ============================================

    /**
     * Generate a unique patient number.
     */
    public static function generatePatientNumber($enterpriseId)
    {
        $enterprise = Enterprise::find($enterpriseId);
        $prefix = $enterprise ? strtoupper(substr($enterprise->name, 0, 3)) : 'HSP';
        
        $lastPatient = self::where('enterprise_id', $enterpriseId)
            ->where('patient_number', 'like', $prefix . '%')
            ->orderBy('patient_number', 'desc')
            ->first();

        if ($lastPatient && $lastPatient->patient_number) {
            $lastNumber = (int) substr($lastPatient->patient_number, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get patients formatted for select dropdown.
     */
    public static function toSelectArray($enterpriseId = null)
    {
        $query = self::active();
        
        if ($enterpriseId) {
            $query->where('enterprise_id', $enterpriseId);
        }
        
        $patients = $query->orderBy('first_name')->get();
        $patientsArray = [];
        
        foreach ($patients as $patient) {
            $patientsArray[$patient->id] = $patient->full_name . " - " . $patient->formatted_phone;
        }
        
        return $patientsArray;
    }

    /**
     * Get patients by search term.
     */
    public static function searchPatients($search, $limit = 10)
    {
        return self::search($search)
            ->active()
            ->limit($limit)
            ->get(['id', 'first_name', 'last_name', 'phone_number_1', 'patient_number']);
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Check if patient has active consultations.
     */
    public function hasActiveConsultations()
    {
        return $this->consultations()
            ->whereIn('status', ['Pending', 'In Progress'])
            ->exists();
    }

    /**
     * Get patient's last consultation.
     */
    public function getLastConsultation()
    {
        return $this->consultations()
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get patient's total medical costs.
     */
    public function getTotalMedicalCosts()
    {
        return $this->billingItems()->sum('total_amount');
    }

    /**
     * Get patient's outstanding payments.
     */
    public function getOutstandingPayments()
    {
        $totalBilled = $this->billingItems()->sum('total_amount');
        $totalPaid = $this->paymentRecords()->sum('amount');
        
        return max(0, $totalBilled - $totalPaid);
    }

    /**
     * Format patient information for display.
     */
    public function getDisplayInfo()
    {
        return [
            'id' => $this->id,
            'patient_number' => $this->patient_number,
            'full_name' => $this->full_name,
            'age' => $this->age,
            'gender' => $this->gender,
            'phone' => $this->formatted_phone,
            'email' => $this->email,
            'last_visit' => $this->getLastConsultation()?->created_at?->format('Y-m-d'),
            'outstanding_balance' => $this->getOutstandingPayments()
        ];
    }
}

<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use App\Traits\DataEncryption;
use Encore\Admin\Form\Field\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as RelationsBelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Encore\Admin\Auth\Database\Administrator;


class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;
    use EnterpriseScopeTrait;
    use StandardBootTrait;
    use DataEncryption;

    protected $fillable = [
        'enterprise_id',
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'username',
        'phone_number_1',
        'phone_number_2',
        'date_of_birth',
        'place_of_birth',
        'sex',
        'home_address',
        'current_address',
        'nationality',
        'religion',
        'spouse_name',
        'spouse_phone',
        'father_name',
        'father_phone',
        'mother_name',
        'mother_phone',
        'languages',
        'emergency_person_name',
        'emergency_person_phone',
        'emergency_contact_relationship',
        'national_id_number',
        'passport_number',
        'tin',
        'nssf_number',
        'bank_name',
        'bank_account_number',
        'marital_status',
        'title',
        'company_id',
        'user_type',
        'patient_status',
        'avatar',
        'intro',
        'rate',
        'belongs_to_company',
        'card_status',
        'card_number',
        'card_balance',
        'card_accepts_credit',
        'card_max_credit',
        'card_accepts_cash',
        'is_dependent',
        'dependent_status',
        'dependent_id',
        'card_expiry',
        'belongs_to_company_status',
        // Medical fields
        'medical_history',
        'allergies',
        'current_medications',
        'blood_type',
        'height',
        'weight',
        'insurance_provider',
        'insurance_policy_number',
        'insurance_expiry_date',
        'family_doctor_name',
        'family_doctor_phone',
        'employment_status',
        'employer_name',
        'annual_income',
        'education_level',
        'preferred_language'
    ];

    /**
     * Fields that should be encrypted for data protection
     *
     * @var array
     */
    protected $encryptedFields = [
        'home_address',
        'current_address',
        'emergency_person_name',
        'emergency_person_phone',
        'spouse_phone',
        'father_phone',
        'mother_phone',
        'medical_history',
        'allergies',
        'current_medications',
        'insurance_policy_number',
        'family_doctor_name',
        'family_doctor_phone',
        'annual_income'
    ];

    /**
     * Fields that should be hashed (one-way encryption)
     *
     * @var array
     */
    protected $hashedFields = [
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'card_expiry' => 'date',
        'card_balance' => 'decimal:2',
        'card_max_credit' => 'decimal:2',
        'card_accepts_cash' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Query scope for patients only
     */
    public function scopePatients($query)
    {
        return $query->where('user_type', 'patient');
    }

    /**
     * Query scope for doctors only
     */
    public function scopeDoctors($query)
    {
        return $query->where('user_type', 'doctor');
    }

    /**
     * Query scope for nurses only
     */
    public function scopeNurses($query)
    {
        return $query->where('user_type', 'nurse');
    }

    /**
     * Query scope for administrative staff
     */
    public function scopeAdministrators($query)
    {
        return $query->where('user_type', 'administrator');
    }

    /**
     * Query scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Query scope for users by gender
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('sex', $gender);
    }

    /**
     * Query scope for users by age range
     */
    public function scopeByAgeRange($query, $minAge, $maxAge = null)
    {
        $query->whereNotNull('date_of_birth');
        $query->where('date_of_birth', '<=', now()->subYears($minAge));
        
        if ($maxAge) {
            $query->where('date_of_birth', '>=', now()->subYears($maxAge));
        }
        
        return $query;
    }

    /**
     * Query scope for searching users
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhere('phone_number_1', 'like', "%{$search}%")
              ->orWhere('phone_number_2', 'like', "%{$search}%")
              ->orWhere('national_id_number', 'like', "%{$search}%");
              
            // Only search email if the column exists
            try {
                $q->orWhere('email', 'like', "%{$search}%");
            } catch (\Exception $e) {
                // Ignore if email column doesn't exist
            }
        });
    }


    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {

            // Only process email if the field exists (some tables like admin_users don't have email)
            if (isset($m->email)) {
                $m->email = trim($m->email);
                if ($m->email != null && strlen($m->email) > 3) {
                    if (!Utils::validateEmail($m->email)) {
                        // throw new \Exception("Invalid email address");
                    } else {
                        //check if email exists
                        $u = null;
                        try {
                            $u = User::withoutGlobalScope('enterprise')->where('email', $m->email)->first();
                        } catch (\Exception $e) {
                            // Email column doesn't exist, skip this check
                        }
                        
                        if ($u != null) {
                            throw new \Exception("Email already exists");
                        }
                        
                        //check if username exists
                        $u = User::withoutGlobalScope('enterprise')->where('username', $m->email)->first();
                        if ($u != null) {
                            throw new \Exception("Email as Username already exists");
                        }
                    }
                }
                // Set username to email only if email exists
                $m->username = $m->email;
            }

            // Build name from first_name and last_name if they exist
            if (isset($m->first_name) && isset($m->last_name)) {
                $n = $m->first_name . " " . $m->last_name;
                if (strlen(trim($n)) > 1) {
                    $m->name = trim($n);
                }
            }
            
            if ($m->password == null || strlen($m->password) < 2) {
                $m->password = password_hash('4321', PASSWORD_DEFAULT);
            }

            $username = null;
            // Handle phone number validation if phone_number_1 exists
            if (isset($m->phone_number_1)) {
                $phone = trim($m->phone_number_1);
                if (strlen($phone) > 2) {
                    $phone = Utils::prepare_phone_number($phone);
                    if (Utils::phone_number_is_valid($phone)) {
                        $username = $phone;
                        $m->phone_number_1 = $phone;
                        //check if username exists
                        $u = User::where('phone_number_1', $phone)->first();
                        if ($u != null) {
                            throw new \Exception("Phone number already exists");
                        }
                        //check if username exists
                        $u = User::where('phone_number_2', $phone)->first();
                        if ($u != null) {
                            throw new \Exception("Phone number already exists as username.");
                        }
                    }
                }
            }

            //check if $username is null or empty and try to use email if available
            if ($username == null && isset($m->email)) {
                //check if email is valid and set it as username using var_filter
                $email = trim($m->email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $username = $email;
                }
            }
            
            // If no username is set yet, and we have username field available, keep it
            if ($username == null && isset($m->username) && strlen($m->username) > 1) {
                $username = $m->username;
            }
            
            // Set a default username if still null (for admin_users table)
            if ($username == null || strlen($username) < 2) {
                if (!isset($m->username) || strlen($m->username) < 2) {
                    throw new \Exception("Invalid username.");
                }
                $username = $m->username;
            }

            //check if username exists
            $u = User::where('username', $username)->first();
            if ($u != null) {
                throw new \Exception("Username already exists");
            }
            $m->username = $username;

            //check if card_status is not set
            if ($m->card_status == null) {
                $m->card_status = "Inactive";
            }

            if ($m->card_status == "Active") {
                if ($m->card_number == null || strlen($m->card_number) < 2) {
                    $m->card_number = date('Y') . rand(100000, 999999) . rand(100000, 999999);
                    //check if card_number exists
                    $u = User::where('card_number', $m->card_number)->first();
                    if ($u != null) {
                        $m->card_number = date('Y') . rand(100000, 999999) . rand(100000, 999999);
                    }
                    //check if card_number exists
                    $u = User::where('card_number', $m->card_number)->first();
                    if ($u != null) {
                        $m->card_number = date('Y') . rand(100000, 999999) . rand(100000, 999999);
                    }
                    //check if card_number exists
                    $u = User::where('card_number', $m->card_number)->first();
                    if ($u != null) {
                        throw new \Exception("Card number already exists.");
                    }
                }

                if ($m->card_expiry == null || strlen($m->card_expiry) < 2) {
                    $m->card_expiry = date('Y-m-d', strtotime('+2 year'));
                }
                //card_accepts_credit
                if ($m->card_accepts_credit == null) {
                    $m->card_accepts_credit = 'No';
                }

                //card_max_credit
                if ($m->card_max_credit == null) {
                    $m->card_max_credit = 0;
                }
                //is_dependent
                if ($m->is_dependent == null) {
                    $m->is_dependent = 'No';
                }
                //dependent_status
                if ($m->dependent_status == null) {
                    $m->dependent_status = 'Inactive';
                }
                if ($m->is_dependent == 'Yes') {
                    $u = User::find($m->dependent_id);
                    if ($u == null) {
                        throw new \Exception("Dependent not found.");
                    }
                }
                //card_expiry
                if ($m->card_expiry == null) {
                    $m->card_expiry = date('Y-m-d', strtotime('+2 year'));
                }
                //belongs_to_company_status
                if ($m->belongs_to_company_status == null) {
                    $m->belongs_to_company_status = 'Inactive';
                }
            }
        });


        self::updating(function ($m) {

            // Only process email if the field is available in the model
            if (isset($m->email)) {
                $m->email = trim($m->email);
                if ($m->email != null && strlen($m->email) > 3) {
                    if (!Utils::validateEmail($m->email)) {
                        // throw new \Exception("Invalid email address");
                    } else {
                        //check if email exists
                        $u = null;
                        try {
                            $u = User::where('email', $m->email)->first();
                        } catch (\Exception $e) {
                            // Email column doesn't exist, skip this check
                        }
                        
                        if ($u != null && $u->id != $m->id) {
                            throw new \Exception("Email already exists");
                        }
                        
                        //check if username exists
                        $u = User::where('username', $m->email)->first();
                        if ($u != null && $u->id != $m->id) {
                            throw new \Exception("Email as Username already exists");
                        }
                    }
                }
                $m->username = $m->email;
            }

            // Build name from first_name and last_name if they exist
            if (isset($m->first_name) && isset($m->last_name)) {
                $n = $m->first_name . " " . $m->last_name;
                if (strlen(trim($n)) > 1) {
                    $m->name = trim($n);
                }
            }
            
            if ($m->password == null || strlen($m->password) < 2) {
                $m->password = password_hash('4321', PASSWORD_DEFAULT);
            }

            $username = null;
            // Handle phone number validation if phone_number_1 exists
            if (isset($m->phone_number_1)) {
                $phone = trim($m->phone_number_1);
                if (strlen($phone) > 2) {
                    $phone = Utils::prepare_phone_number($phone);
                    if (Utils::phone_number_is_valid($phone)) {
                        $username = $phone;
                        $m->phone_number_1 = $phone;
                        //check if username exists
                        $u = User::where('phone_number_1', $phone)->first();
                        if ($u != null && $u->id != $m->id) {
                            throw new \Exception("Phone number already exists");
                        }
                        //check if username exists
                        $u = User::where('phone_number_2', $phone)->first();
                        if ($u != null) {
                            throw new \Exception("Phone number already exists as username.");
                        }
                    }
                }
            }

            //check if $username is null or empty and try to use email if available
            if ($username == null && isset($m->email)) {
                //check if email is valid and set it as username using var_filter
                $email = trim($m->email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $username = $email;
                }
            }
            //check if $username is null or empty
            if ($username == null || strlen($username) < 2) {
                throw new \Exception("Invalid username.");
            }

            //check if username exists
            $u = User::where('username', $username)->first();
            if ($u != null && $u->id != $m->id) {
                throw new \Exception("Username already exists");
            }
            $m->username = $username;

            //check if card_status is not set
            if ($m->card_status == null) {
                $m->card_status = "Inactive";
            }

            if ($m->card_status == "Active") {
                if ($m->card_number == null || strlen($m->card_number) < 2) {
                    $m->card_number = date('Y') . rand(100000, 999999) . rand(100000, 999999);
                    //check if card_number exists
                    $u = User::where('card_number', $m->card_number)->first();
                    if ($u != null && $u->id != $m->id) {
                        $m->card_number = date('Y') . rand(100000, 999999) . rand(100000, 999999);
                    }
                    //check if card_number exists
                    $u = User::where('card_number', $m->card_number)->first();
                    if ($u != null && $u->id != $m->id) {
                        $m->card_number = date('Y') . rand(100000, 999999) . rand(100000, 999999);
                    }
                    //check if card_number exists
                    $u = User::where('card_number', $m->card_number)->first();
                    if ($u != null && $u->id != $m->id) {
                        throw new \Exception("Card number already exists.");
                    }
                }

                if ($m->card_expiry == null || strlen($m->card_expiry) < 2) {
                    $m->card_expiry = date('Y-m-d', strtotime('+2 year'));
                }
                //card_accepts_credit
                if ($m->card_accepts_credit == null) {
                    $m->card_accepts_credit = 'No';
                }

                //card_max_credit
                if ($m->card_max_credit == null) {
                    $m->card_max_credit = 0;
                }
                //is_dependent
                if ($m->is_dependent == null) {
                    $m->is_dependent = 'No';
                }
                //dependent_status
                if ($m->dependent_status == null) {
                    $m->dependent_status = 'Inactive';
                }
                if ($m->is_dependent == 'Yes') {
                    $u = User::find($m->dependent_id);
                    if ($u == null) {
                        throw new \Exception("Dependent not found.");
                    }
                }
                //card_expiry
                if ($m->card_expiry == null) {
                    $m->card_expiry = date('Y-m-d', strtotime('+2 year'));
                }
                //belongs_to_company_status
                if ($m->belongs_to_company_status == null) {
                    $m->belongs_to_company_status = 'Inactive';
                }
            }
        });
    }



    public function send_password_reset()
    {
        $u = $this;
        $u->stream_id = rand(100000, 999999);
        $u->save();
        $data['email'] = $u->email;
        $data['name'] = $u->name;
        $data['subject'] = env('APP_NAME') . " - Password Reset";
        $data['body'] = "<br>Dear " . $u->name . ",<br>";
        $data['body'] .= "<br>Please click the link below to reset your " . env('APP_NAME') . " System password.<br><br>";
        $data['body'] .= url('reset-password') . "?token=" . $u->stream_id . "<br>";
        $data['body'] .= "<br>Thank you.<br><br>";
        $data['body'] .= "<br><small>This is an automated message, please do not reply.</small><br>";
        $data['view'] = 'mail-1';
        $data['data'] = $data['body'];
        try {
            Utils::mail_sender($data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public static function update_rating($id)
    {
        $user = User::find($id);
        /* $tasks = Task::where('assigned_to', $id)->get();
        $rate = 0;
        $count = 0;
        foreach ($tasks as $task) {
            if ($task->manager_submission_status != 'Not Submitted') {
                $rate += $task->rate;
                $count++;
            }
        }
        if ($count > 0) {
            $rate = $rate / $count;
        } */
        $work_load_pending = Task::where('assigned_to', $id)->where('manager_submission_status', 'Not Submitted')
            ->sum('hours');
        $work_load_completed = Task::where('assigned_to', $id)->where('manager_submission_status', 'Done')
            ->sum('hours');
        $user->work_load_pending = $work_load_pending;
        $user->work_load_completed = $work_load_completed;
        $user->save();
    }


    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }

    /**
     * Get the column name for the "username" (used for login).
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Relationship to consultations (for patients)
     */
    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'patient_id');
    }

    /**
     * Relationship to consultations as doctor
     */
    public function doctorConsultations()
    {
        return $this->hasMany(Consultation::class, 'doctor_id');
    }

    /**
     * Relationship to medical services (for patients)
     */
    public function medicalServices()
    {
        return $this->belongsToMany(MedicalService::class, 'medical_service_items')
                    ->withPivot(['quantity', 'price', 'total'])
                    ->withTimestamps();
    }

    /**
     * Relationship to billing items (for patients)
     */
    public function billingItems()
    {
        return $this->hasMany(BillingItem::class, 'patient_id');
    }

    /**
     * Relationship to payment records (for patients)
     */
    public function paymentRecords()
    {
        return $this->hasMany(PaymentRecord::class, 'patient_id');
    }

    /**
     * Relationship to the dependent user (if this user is a dependent)
     */
    public function dependentOf()
    {
        return $this->belongsTo(User::class, 'dependent_id');
    }

    /**
     * Relationship to dependents (users who are dependents of this user)
     */
    public function dependents()
    {
        return $this->hasMany(User::class, 'dependent_id');
    }

    /**
     * Relationship to enterprise
     */
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * Get upcoming appointments (consultations) for patient
     */
    public function upcomingAppointments()
    {
        return $this->consultations()
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->where('appointment_date', '>', now())
            ->orderBy('appointment_date');
    }

    /**
     * Get active consultations for patient
     */
    public function activeConsultations()
    {
        return $this->consultations()->whereIn('status', ['scheduled', 'confirmed', 'in_progress']);
    }

    /**
     * Calculate BMI if height and weight are available
     */
    public function calculateBMI()
    {
        if ($this->height && $this->weight) {
            $heightInMeters = $this->height / 100; // Convert cm to meters
            return round($this->weight / ($heightInMeters * $heightInMeters), 2);
        }
        return null;
    }

    /**
     * Get patient's medical summary
     */
    public function getMedicalSummary()
    {
        return [
            'allergies' => $this->allergies,
            'current_medications' => $this->current_medications,
            'medical_history' => $this->medical_history,
            'blood_type' => $this->blood_type ?? null,
            'height' => $this->height,
            'weight' => $this->weight,
            'bmi' => $this->calculateBMI(),
            'insurance_provider' => $this->insurance_provider,
            'insurance_policy_number' => $this->insurance_policy_number,
            'insurance_expiry_date' => $this->insurance_expiry_date,
        ];
    }

    /**
     * Get patient's emergency contact info
     */
    public function getEmergencyContact()
    {
        return [
            'name' => $this->emergency_person_name,
            'phone' => $this->emergency_person_phone,
            'relationship' => $this->emergency_contact_relationship ?? null,
        ];
    }

    /**
     * Check if patient has active insurance
     */
    public function hasActiveInsurance()
    {
        return $this->insurance_provider && 
               $this->insurance_expiry_date && 
               $this->insurance_expiry_date->isFuture();
    }


    //appends
    protected $appends = ['short_name', 'full_name', 'age', 'formatted_phone'];

    public function getShortNameAttribute()
    {
        //in this formart - J. Doe from first_name and last_name
        if (strlen($this->first_name) > 1) {
            $letter_1 = substr($this->first_name, 0, 1);
        } else {
            $letter_1 = $this->first_name;
        }
        return $letter_1 . ". " . $this->last_name;
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get age attribute
     */
    public function getAgeAttribute()
    {
        if (!$this->date_of_birth) {
            return null;
        }
        
        // Convert to Carbon if it's a string
        $birthDate = $this->date_of_birth instanceof \Carbon\Carbon 
            ? $this->date_of_birth 
            : \Carbon\Carbon::parse($this->date_of_birth);
            
        return $birthDate->age;
    }

    /**
     * Get formatted phone attribute
     */
    public function getFormattedPhoneAttribute()
    {
        return $this->phone_number_1 ?: $this->phone_number_2 ?: 'N/A';
    }

    /**
     * Get patient number for patients
     */
    public function getPatientNumberAttribute()
    {
        if ($this->user_type === 'patient') {
            $id = $this->id ?: 0;
            return 'PAT-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
        }
        return null;
    }

    /**
     * Check if user is a patient
     */
    public function isPatient()
    {
        return $this->user_type === 'patient';
    }

    /**
     * Check if user is a doctor
     */
    public function isDoctor()
    {
        return $this->user_type === 'doctor';
    }

    /**
     * Check if user is a nurse
     */
    public function isNurse()
    {
        return $this->user_type === 'nurse';
    }

    /**
     * Check if user is an administrator
     */
    public function isAdministrator()
    {
        return $this->user_type === 'administrator';
    }

    /**
     * Check if user has active consultations
     */
    public function hasActiveConsultations()
    {
        if (!$this->isPatient()) {
            return false;
        }
        
        return $this->consultations()
                    ->where('status', 'active')
                    ->exists();
    }

    /**
     * Get total medical costs for patient
     */
    public function getTotalMedicalCosts()
    {
        if (!$this->isPatient()) {
            return 0;
        }
        
        return $this->billingItems()->sum('total');
    }

    /**
     * Get outstanding payments for patient
     */
    public function getOutstandingPayments()
    {
        if (!$this->isPatient()) {
            return 0;
        }
        
        $totalBilled = $this->getTotalMedicalCosts();
        $totalPaid = $this->paymentRecords()->sum('amount');
        
        return max(0, $totalBilled - $totalPaid);
    }

    /**
     * Get latest consultation for patient
     */
    public function getLatestConsultation()
    {
        if (!$this->isPatient()) {
            return null;
        }
        
        return $this->consultations()
                    ->latest()
                    ->first();
    }

    /**
     * Static method to get patients for select dropdown
     */
    public static function getPatientsForSelect()
    {
        return static::patients()
                    ->orderBy('first_name')
                    ->get()
                    ->pluck('full_name', 'id')
                    ->toArray();
    }

    /**
     * Static method to get doctors for select dropdown
     */
    public static function getDoctorsForSelect()
    {
        return static::doctors()
                    ->orderBy('first_name')
                    ->get()
                    ->pluck('full_name', 'id')
                    ->toArray();
    }

    /**
     * Static method to search patients
     */
    public static function searchPatients($search)
    {
        return static::patients()
                    ->search($search)
                    ->orderBy('first_name')
                    ->get();
    }

    //get doctors list

    public static function getDoctors()
    {
        $users = [];
        foreach (
            User::doctors()
                ->orderBy('name', 'asc')
                ->get() as $key => $value
        ) {
            $users[$value->id] = $value->name;
        }
        return $users;
    }

    //get card
    public function getCard()
    {
        if ($this->is_dependent == 'Yes') {
            $c = User::find($this->dependent_id);
            return $c;
        } else {
            return $this;
        }
    }
}

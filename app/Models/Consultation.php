<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class Consultation extends Model
{
    use HasFactory;
    use EnterpriseScopeTrait;
    use StandardBootTrait;

    protected $fillable = [
        'enterprise_id',
        'consultation_number',
        'patient_id',
        'patient_name',
        'patient_contact',
        'contact_address',
        'receptionist_id',
        'consultation_fee',
        'request_date',
        'request_remarks',
        'receptionist_comment',
        'status',
        'main_status',
        'company_id',
        'preferred_date_and_time',
        'services_requested',
        'reason_for_consultation',
        'main_remarks',
        'request_status',
        'temperature',
        'weight',
        'height',
        'bmi',
        'total_charges',
        'total_paid',
        'total_due',
        'payemnt_status',
        'subtotal',
        'fees_total',
        'discount',
        'invoice_processed',
        'invoice_pdf',
        'invoice_process_date',
        'bill_status',
        'specify_specialist',
        'specialist_id',
        'report_link',
        'dosage_progress',
        'dosage_is_completed',
        // Enhanced appointment scheduling fields
        'doctor_id',
        'department_id',
        'appointment_date',
        'appointment_end_date', 
        'duration_minutes',
        'appointment_type',
        'priority',
        'room_id',
        'equipment_ids',
        'is_recurring',
        'recurrence_type',
        'recurrence_interval',
        'recurrence_end_date',
        'parent_consultation_id',
        'preparation_instructions',
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

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'request_date' => 'datetime',
        'preferred_date_and_time' => 'datetime',
        'invoice_process_date' => 'datetime',
        'temperature' => 'decimal:1',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'bmi' => 'decimal:2',
        'total_charges' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'total_due' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'fees_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'consultation_fee' => 'decimal:2',
        'invoice_processed' => 'boolean',
        'dosage_is_completed' => 'boolean',
        // Enhanced appointment scheduling casts
        'appointment_date' => 'datetime',
        'appointment_end_date' => 'datetime',
        'recurrence_end_date' => 'date',
        'is_recurring' => 'boolean',
        'sms_reminder_sent' => 'boolean',
        'email_reminder_sent' => 'boolean',
        'reminder_sent_at' => 'datetime',
        'confirmation_required' => 'boolean',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'equipment_ids' => 'array',
    ];

    /**
     * Query scope for active consultations
     */
    public function scopeActive($query)
    {
        return $query->where('main_status', 'Active');
    }

    /**
     * Query scope for completed consultations
     */
    public function scopeCompleted($query)
    {
        return $query->where('main_status', 'Completed');
    }

    /**
     * Query scope for pending consultations
     */
    public function scopePending($query)
    {
        return $query->where('main_status', 'Pending');
    }

    /**
     * Query scope for cancelled consultations
     */
    public function scopeCancelled($query)
    {
        return $query->where('main_status', 'Cancelled');
    }

    /**
     * Query scope for paid consultations
     */
    public function scopePaid($query)
    {
        return $query->where('payemnt_status', 'Paid');
    }

    /**
     * Query scope for unpaid consultations
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payemnt_status', '!=', 'Paid');
    }

    /**
     * Query scope for consultations by doctor
     */
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('specialist_id', $doctorId);
    }

    /**
     * Query scope for consultations by patient
     */
    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Query scope for consultations within date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Query scope for today's consultations
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Query scope for this month's consultations
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }


    /**
     * Boot the model with standardized event handling.
     */
    protected static function boot(): void
    {
        parent::boot();
    }

    /**
     * Custom logic before creating/updating a consultation.
     */
    protected function processCustomBeforeSave(): void
    {
        $this->prepareConsultationData();
    }

    /**
     * Custom logic before deleting a consultation.
     */
    protected function processCustomBeforeDelete(): void
    {
        // Delete related medical services
        $medical_services = $this->medicalServices;
        foreach ($medical_services as $medical_service) {
            $medical_service->delete();
        }

        // Delete related billing items
        $billing_items = $this->billingItems;
        foreach ($billing_items as $billing_item) {
            $billing_item->delete();
        }

        // Delete related payment records
        $payment_records = $this->paymentRecords;
        foreach ($payment_records as $payment_record) {
            $payment_record->delete();
        }
    }

    /**
     * Prepare consultation data.
     */
    private function prepareConsultationData(): void
    {
        $this->validatePatient();
        $this->generateConsultationNumber();
        $this->setDefaultValues();
        $this->calculateTotals();
    }

    /**
     * Validate that patient exists.
     */
    private function validatePatient(): void
    {
        $patient = User::withoutGlobalScope('enterprise')->find($this->patient_id);
        if (!$patient) {
            throw new \Exception('Patient not found');
        }
    }

    /**
     * Generate consultation number if not set.
     */
    private function generateConsultationNumber(): void
    {
        if (!$this->consultation_number) {
            $this->consultation_number = 'CON-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Set default values for consultation.
     */
    private function setDefaultValues(): void
    {
        if (!$this->request_date) {
            $this->request_date = now();
        }

        if (!$this->main_status) {
            $this->main_status = 'Pending';
        }

        if (!$this->request_status) {
            $this->request_status = 'Pending';
        }

        if (!$this->bill_status) {
            $this->bill_status = 'Pending';
        }

        if (!$this->payemnt_status) {
            $this->payemnt_status = 'Pending';
        }
    }

    /**
     * Calculate consultation totals.
     */
    private function calculateTotals(): void
    {
        // This can be expanded based on business logic
        if (!$this->total_charges && $this->consultation_fee) {
            $this->total_charges = $this->consultation_fee;
        }
    }

    static function do_prepare($model)
    {
        $patient = User::withoutGlobalScope('enterprise')->find($model->patient_id);
        if ($patient == null) {
            throw new \Exception('Patient not found');
        }
        $patient->company_id = $model->company_id;
        $receptionist = User::withoutGlobalScope('enterprise')->find($model->receptionist_id);
        $loggedUser = Auth::user();
        if ($loggedUser == null) {
            $loggedUser = $patient;
            //throw new \Exception('You are not logged in.');
        }
        if ($receptionist == null) {
            $model->receptionist_id = $loggedUser->id;
        }
        $model->company_id = $loggedUser->company_id;
        $model->patient_name = $patient->name;
        $model->patient_contact = $patient->phone_number_1;
        $model->contact_address = $patient->current_address;

        if ($model->consultation_number == null || strlen($model->consultation_number) < 2) {
            $model->consultation_number = Consultation::generate_consultation_number();
        }

        return $model;
    }

    //static function that generates consultation_number
    static function generate_consultation_number()
    {
        //formart year-month-day-number_of_consultations_of_the_month
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $count = Consultation::where([
            'created_at' => date('Y-m-d')
        ])->count();
        return $year . '-' . $month . '-' . $day . '-' . ($count + 1);
    }

    //has many MedicalService
    public function medicalServices()
    {
        return $this->hasMany(MedicalService::class);
    }

    //has many DoseItem
    public function doseItems()
    {
        return $this->hasMany(DoseItem::class);
    }

    //has many BillingItem
    public function billingItems()
    {
        return $this->hasMany(BillingItem::class);
    }

    //belongs to patient_id
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    //belongs to receptionist_id
    public function receptionist()
    {
        return $this->belongsTo(User::class, 'receptionist_id');
    }

    //belongs to specialist_id or doctor_id (for backwards compatibility and appointment functionality)
    public function doctor()
    {
        // Support both old specialist_id and new doctor_id for appointment functionality
        $doctorId = $this->doctor_id ?? $this->specialist_id;
        return $this->belongsTo(User::class, 'doctor_id')->orWhere('id', $this->specialist_id);
    }

    //belongs to specialist_id
    public function specialist()
    {
        return $this->belongsTo(User::class, 'specialist_id');
    }

    //belongs to company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    //belongs to enterprise
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * Get the consultation's formatted number
     */
    public function getFormattedNumberAttribute()
    {
        return 'CONS-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the consultation's duration in days
     */
    public function getDurationAttribute()
    {
        if (!$this->created_at) {
            return 0;
        }
        return $this->created_at->diffInDays(now());
    }

    /**
     * Check if consultation is overdue
     */
    public function getIsOverdueAttribute()
    {
        if ($this->main_status === 'Completed') {
            return false;
        }
        
        return $this->created_at->diffInDays(now()) > 7; // 7 days threshold
    }

    /**
     * Get BMI category
     */
    public function getBmiCategoryAttribute()
    {
        if (!$this->bmi) {
            return null;
        }
        
        if ($this->bmi < 18.5) {
            return 'Underweight';
        } elseif ($this->bmi < 25) {
            return 'Normal';
        } elseif ($this->bmi < 30) {
            return 'Overweight';
        } else {
            return 'Obese';
        }
    }

    /**
     * Calculate BMI automatically
     */
    public function calculateBmi()
    {
        if ($this->weight && $this->height) {
            $heightInMeters = $this->height / 100;
            $this->bmi = $this->weight / ($heightInMeters * $heightInMeters);
        }
    }

    /**
     * Check if consultation has outstanding balance
     */
    public function hasOutstandingBalance()
    {
        return $this->total_due > 0;
    }

    /**
     * Get payment completion percentage
     */
    public function getPaymentCompletionPercentage()
    {
        if ($this->total_charges <= 0) {
            return 100;
        }
        
        return ($this->total_paid / $this->total_charges) * 100;
    }

    //detail getter
    public function getDetailAttribute()
    {
        return 'this->patient->name . ' - ' . $this->patient->phone_number_1';
    }

    public static function process_dosages()
    {
        $doses = DoseItem::where([
            'is_processed' => 'No'
        ])
            ->limit(100)
            ->orderBy('created_at', 'desc')
            ->get();
        foreach ($doses as $dose) {
            $now = Carbon::now();
            for ($day_number = 0; $day_number < $dose->number_of_days; $day_number++) {
                $due_date = $now->addDays($day_number)->format('Y-m-d');
                for ($x = 1; $x <= $dose->times_per_day; $x++) {

                    $dose_record = new DoseItemRecord();
                    $dose_record->consultation_id = $dose->consultation_id;
                    $dose_record->medicine = $dose->medicine;
                    $dose_record->quantity = $dose->quantity;
                    $dose_record->units = $dose->units;
                    $dose_record->times_per_day = $dose->times_per_day;
                    $dose_record->number_of_days = $dose->number_of_days;
                    $dose_record->status = 'Not taken';
                    $dose_record->remarks = null;
                    $dose_record->due_date = $due_date;
                    $dose_record->time_value = $x;
                    $dose_record->date_submitted = null;
                    $dose_record->dose_item_id = $dose->id;
                    if ($x == 1) {
                        $dose_record->time_name = 'Morning';
                    } else if ($x == 2) {
                        $dose_record->time_name = 'Afternoon';
                    } else if ($x == 3) {
                        $dose_record->time_name = 'Evening';
                    } else if ($x == 4) {
                        $dose_record->time_name = 'Night';
                    }

                    //check if exists before saving
                    $exsisting = DoseItemRecord::where([
                        'dose_item_id' => $dose->id,
                        'time_name' => $dose_record->time_name,
                        'due_date' => $dose_record->due_date,
                    ])->first();
                    if ($exsisting != null) {
                        continue;
                    }
                    $dose_record->save();
                }
            }
            $dose->is_processed = 'Yes';
            $dose->save();
        }
    }

    public static function process_ongoing_consultations()
    {
        $consultations = Consultation::where('main_status', 'Ongoing')
            ->limit(100)
            ->orderBy('created_at', 'desc')
            ->get();
        foreach ($consultations as $consultation) {
            $consultation->process_invoice();
            $consultation->process_payment_status();
            $consultation->process_balance();
            $consultation->process_stock_records();
        }
    }

    public function process_stock_records()
    {
        if ($this->main_status == 'Completed') {
            return;
        }
        if ($this->bill_status != 'Ready for Billing') {
            return;
        }

        $amount_paid = PaymentRecord::where([
            'consultation_id' => $this->id,
            'payment_status' => 'Success'
        ])->sum('amount_paid');

        //loop through medical_services
        $medical_services = $this->medicalServices;
        foreach ($medical_services as $key => $val) {
            if (count($val->medicalServiceItems) < 1) {
                continue;
            }
            //loop through $val->medicalServiceItems) 
            foreach ($val->medicalServiceItems as $service_item) {
                $stock_record = StockOutRecord::where([
                    'consultation_id' => $this->id,
                    'medical_service_id' => $val->id,
                    'stock_item_id' => $service_item->id
                ])->first();
                if ($stock_record == null) {
                    $stock_record = new StockOutRecord();
                }
                $stock_item = StockItem::find($service_item->stock_item_id);
                if ($stock_item == null) {
                    continue;
                }

                $stock_record->consultation_id = $this->id;
                $stock_record->medical_service_id = $val->id;
                $stock_record->stock_item_id = $stock_item->id;
                $stock_record->stock_item_category_id = $stock_item->stock_item_category_id;
                $stock_record->unit_price = $stock_item->sale_price;
                $stock_record->quantity = $service_item->quantity;
                $stock_record->total_price = $service_item->total_price;
                $stock_record->quantity_after = $stock_item->quantity - $service_item->quantity;
                $stock_record->description = $service_item->description;
                $stock_record->measuring_unit = $stock_item->measuring_unit;
                $stock_record->due_date = date('Y-m-d H:i:s');
                $stock_record->details = "Stock out for " . $val->type . " - " . $val->remarks . ".";
                $stock_record->save();
            }
        }
    }

    public function process_balance()
    {
        if ($this->main_status == 'Completed') {
            return;
        }

        $amount_paid = PaymentRecord::where([
            'consultation_id' => $this->id,
            'payment_status' => 'Success'
        ])->sum('amount_paid');

        $this->total_paid = $amount_paid;
        $this->total_due = $this->total_charges - $amount_paid;
        if (($this->total_due <= 0) && ($this->total_paid >= 500)) {
            $this->payemnt_status = 'Paid';
            $this->main_status = 'Completed';
        } else {
            $this->payemnt_status = 'Not Paid';
        }
        $this->save();
    }


    public function report_exists()
    {
        if ($this->report_link == null) {
            return false;
        }
        if (strlen($this->report_link) < 3) {
            return false;
        }
        $splits =  explode('/', $this->report_link);
        if (count($splits) < 2) {
            return false;
        }
        $last = $splits[count($splits) - 1];
        $path = public_path('storage/files/' . $last);
        return file_exists($path);
    }
    public function process_report()
    {
        $exists = $this->report_exists();
        if (!$exists) {
            if ($this->main_status == 'Complete') {
                return;
            }
        }

        $company = Company::find(1);
        $pdf = App::make('dompdf.wrapper');
        $pdf->set_option('enable_html5_parser', TRUE);
        if (isset($_GET['html'])) {
            return view('medical-report', [
                'item' => $this,
                'company' => $company,
            ])->render();
        }
        $pdf->loadHTML(view('medical-report', [
            'item' => $this,
            'company' => $company,
        ])->render());
        $file_name = 'report-' . $this->consultation_number . '.pdf';
        $file_path = public_path('storage/files/' . $file_name);
        //check if file exists
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $pdf->save($file_path);
        $this->report_link = 'files/' . $file_name;
        $this->save();
    }
    public function process_invoice()
    {
        if ($this->main_status == 'Complete') {
            return;
        }

        $medical_services = $this->medicalServices;
        $subtotal = 0;
        foreach ($medical_services as $medical_service) {
            $isFirst = true;
            $medical_service->remarks = null;
            $medical_service->total_price = 0;

            foreach ($medical_service->medicalServiceItems as $medical_service_item) {
                $stock_item = StockItem::find($medical_service_item->stock_item_id);
                if ($stock_item == null) {
                    throw new \Exception('Stock item not found. #' . $medical_service_item->stock_item_id);
                }
                $medical_service_item->remarks = $stock_item->name;
                $medical_service_item->unit_price = $stock_item->sale_price;
                $medical_service_item->total_price = ((float)($medical_service_item->quantity)) * ((float)($stock_item->sale_price));

                if ($isFirst) {
                    $isFirst = false;
                    $medical_service->remarks = '' . $medical_service_item->remarks . ": $stock_item->name: " . (float)($medical_service_item->quantity) . " x " . number_format($stock_item->sale_price) . " = " . number_format($medical_service_item->total_price);
                } else {
                    $medical_service->remarks .= ", $stock_item->name: " . (float)($medical_service_item->quantity) . " x " . number_format($stock_item->sale_price) . " = " . number_format($medical_service_item->total_price);
                }
                $medical_service_item->save();
                $medical_service->total_price += $medical_service_item->total_price;
            }
            $medical_service->save();
            $subtotal += $medical_service->total_price;
        }

        $medical_services = 0;
        $discount = 0;
        foreach ($this->billingItems as $billing_item) {
            if ($billing_item->type == 'Discount') {
                $discount += (float)($billing_item->price);
            } else {
                $medical_services += (float)($billing_item->price);
            }
        }
        $this->fees_total = $medical_services;
        $this->subtotal = $subtotal + $medical_services;
        $this->discount = $discount;
        $this->total_charges = $this->subtotal - $discount;
        $this->save();

        $total_paid  = PaymentRecord::where([
            'consultation_id' => $this->id
        ])->sum('amount_paid');

        $this->total_due = $this->total_charges - $total_paid;
        $this->total_paid = $total_paid;
        //payemnt_status

        if ($this->total_due <= 0) {
            $this->payemnt_status = 'Paid';
        } else {
            $this->payemnt_status = 'Not Paid';
        }
        $this->invoice_processed = 'Yes';
        //date time
        $this->invoice_process_date = date('Y-m-d H:i:s');
        $file_name = $this->consultation_number . '.pdf';
        $file_path = public_path('storage/files/' . $file_name);
        //check if file exists
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $this->invoice_pdf = 'files/' . $file_name;
        $this->save();


        $company = Company::find(1);
        $pdf = App::make('dompdf.wrapper');
        $pdf->set_option('enable_html5_parser', TRUE);
        $pdf->loadHTML(view('invoice', [
            'item' => $this,
            'company' => $company,
        ])->render());

        $pdf->save($file_path);
    }

    //getter for services_text
    public function getServicesTextAttribute()
    {
        $medical_services = $this->medicalServices;
        $text = '';
        $isFirst = true;
        foreach ($medical_services as $medical_service) {
            if ($isFirst) {
                $isFirst = false;
                $text = $medical_service->type;
            } else {
                $text .= ', ' . $medical_service->type;
            }
        }
        return $text;
    }

    //getter for is_ready_for_billing
    public function process_payment_status()
    {
        $medical_services = $this->medicalServices;
        //count $medical_services
        $count = count($medical_services);
        if ($count < 1) {
            return false;
        }
        $isReady = true;
        foreach ($medical_services as $medical_service) {
            if ($medical_service->status != 'Completed') {
                $isReady = false;
                break;
            }
        }
        if ($isReady) {
            $this->bill_status = 'Ready for Billing';
        } else {
            $this->bill_status = 'Not Ready for Billing';
        }
        $this->save();
        return $isReady;
    }

    //has many PaymentRecords
    public function paymentRecords()
    {
        return $this->hasMany(PaymentRecord::class);
    }

    public static function getPayableConsultations()
    {
        $consultations = Consultation::where([
            'main_status' => 'Payment',
        ])->get();
        $data = [];
        foreach ($consultations as $consultation) {
            $data[$consultation->id] = $consultation->name_text;
        }
        return $data;
    }

    //getter for name_text to be consultation ID and name of patient
    public function getNameTextAttribute()
    {
        $name = '';
        if ($this->patient != null) {
            $name = ' - ' . $this->patient->name;
        }
        return $this->consultation_number . " " . $name;
    }

    //has many DrugItemRecords
    public function drugItemRecords()
    {
        return $this->hasMany(DoseItemRecord::class);
    }


    //appends for services_text
    protected $appends = ['services_text', 'name_text'];

    // ============================================
    // APPOINTMENT SCHEDULING METHODS
    // ============================================

    /**
     * Appointment type constants
     */
    public static function getAppointmentTypes()
    {
        return [
            'consultation' => 'Consultation',
            'follow_up' => 'Follow Up',
            'surgery' => 'Surgery',
            'procedure' => 'Procedure',
            'lab_test' => 'Lab Test',
            'imaging' => 'Imaging',
            'therapy' => 'Therapy',
            'vaccination' => 'Vaccination',
            'emergency' => 'Emergency'
        ];
    }

    /**
     * Priority levels
     */
    public static function getPriorityLevels()
    {
        return [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];
    }

    /**
     * Appointment status options
     */
    public static function getAppointmentStatusOptions()
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
     * Check for appointment conflicts
     */
    public function hasConflicts()
    {
        if (!$this->appointment_date || !$this->doctor_id) {
            return false;
        }

        $conflicts = self::where('doctor_id', $this->doctor_id)
            ->where('id', '!=', $this->id)
            ->where('main_status', '!=', 'Cancelled')
            ->where(function($query) {
                $query->whereBetween('appointment_date', [$this->appointment_date, $this->appointment_end_date])
                    ->orWhereBetween('appointment_end_date', [$this->appointment_date, $this->appointment_end_date])
                    ->orWhere(function($q) {
                        $q->where('appointment_date', '<=', $this->appointment_date)
                          ->where('appointment_end_date', '>=', $this->appointment_end_date);
                    });
            })
            ->exists();

        return $conflicts;
    }

    /**
     * Check room availability
     */
    public function isRoomAvailable()
    {
        if (!$this->appointment_date || !$this->room_id) {
            return true;
        }

        $conflicts = self::where('room_id', $this->room_id)
            ->where('id', '!=', $this->id)
            ->where('main_status', '!=', 'Cancelled')
            ->where(function($query) {
                $query->whereBetween('appointment_date', [$this->appointment_date, $this->appointment_end_date])
                    ->orWhereBetween('appointment_end_date', [$this->appointment_date, $this->appointment_end_date]);
            })
            ->exists();

        return !$conflicts;
    }

    /**
     * Generate recurring appointments
     */
    public function generateRecurringAppointments()
    {
        if (!$this->is_recurring || !$this->recurrence_type || !$this->recurrence_end_date) {
            return [];
        }

        $appointments = [];
        $currentDate = Carbon::parse($this->appointment_date);
        $endDate = Carbon::parse($this->recurrence_end_date);
        $interval = $this->recurrence_interval ?: 1;

        while ($currentDate->lte($endDate)) {
            // Move to next occurrence based on recurrence type
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
                $duration = $this->duration_minutes ?: 30;
                $endTime = $currentDate->copy()->addMinutes($duration);

                $appointments[] = [
                    'enterprise_id' => $this->enterprise_id,
                    'consultation_number' => self::generate_consultation_number(),
                    'patient_id' => $this->patient_id,
                    'doctor_id' => $this->doctor_id,
                    'department_id' => $this->department_id,
                    'appointment_date' => $currentDate->toDateTimeString(),
                    'appointment_end_date' => $endTime->toDateTimeString(),
                    'duration_minutes' => $duration,
                    'appointment_type' => $this->appointment_type,
                    'priority' => $this->priority,
                    'reason_for_consultation' => $this->reason_for_consultation,
                    'room_id' => $this->room_id,
                    'is_recurring' => false,
                    'parent_consultation_id' => $this->id,
                    'main_status' => 'Scheduled',
                    'request_status' => 'Pending',
                    'created_by' => $this->created_by,
                ];
            }
        }

        return $appointments;
    }

    /**
     * Calculate appointment end date based on duration
     */
    public function calculateEndDate()
    {
        if ($this->appointment_date && $this->duration_minutes) {
            $this->appointment_end_date = Carbon::parse($this->appointment_date)
                ->addMinutes($this->duration_minutes)
                ->toDateTimeString();
        }
    }

    /**
     * Check if appointment needs reminder
     */
    public function needsReminder()
    {
        if (!$this->appointment_date || $this->reminder_sent_at) {
            return false;
        }

        $appointmentTime = Carbon::parse($this->appointment_date);
        $reminderTime = $appointmentTime->copy()->subHours(24); // 24 hours before

        return now()->gte($reminderTime) && now()->lt($appointmentTime);
    }

    /**
     * Mark reminder as sent
     */
    public function markReminderSent($type = 'both')
    {
        $updates = ['reminder_sent_at' => now()];
        
        if ($type === 'sms' || $type === 'both') {
            $updates['sms_reminder_sent'] = true;
        }
        
        if ($type === 'email' || $type === 'both') {
            $updates['email_reminder_sent'] = true;
        }

        $this->update($updates);
    }

    /**
     * Enhanced scope for scheduled appointments
     */
    public function scopeScheduled($query)
    {
        return $query->where('main_status', 'Scheduled')
                    ->orWhere('main_status', 'Confirmed');
    }

    /**
     * Scope for appointments on a specific date
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('appointment_date', $date);
    }

    /**
     * Scope for appointments for a specific doctor
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope for appointments in a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('appointment_date', [$startDate, $endDate]);
    }

    /**
     * Enhanced relationships for appointment functionality
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function parentConsultation()
    {
        return $this->belongsTo(Consultation::class, 'parent_consultation_id');
    }

    public function recurringConsultations()
    {
        return $this->hasMany(Consultation::class, 'parent_consultation_id');
    }

    /**
     * Get available time slots for a doctor on a specific date
     */
    public static function getAvailableTimeSlots($doctorId, $date, $duration = 30)
    {
        // Get doctor's schedule
        $schedule = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('day_of_week', strtolower(Carbon::parse($date)->format('l')))
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return [];
        }

        // Get existing appointments for the date
        $existingAppointments = self::forDoctor($doctorId)
            ->onDate($date)
            ->where('main_status', '!=', 'Cancelled')
            ->get(['appointment_date', 'appointment_end_date']);

        // Generate time slots
        $slots = [];
        $current = Carbon::parse($date . ' ' . $schedule->start_time);
        $end = Carbon::parse($date . ' ' . $schedule->end_time);

        while ($current->addMinutes($duration)->lte($end)) {
            $slotStart = $current->copy()->subMinutes($duration);
            $slotEnd = $current->copy();

            // Check if slot conflicts with existing appointments
            $hasConflict = $existingAppointments->contains(function ($appointment) use ($slotStart, $slotEnd) {
                $appointmentStart = Carbon::parse($appointment->appointment_date);
                $appointmentEnd = Carbon::parse($appointment->appointment_end_date);

                return $slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart);
            });

            if (!$hasConflict) {
                $slots[] = [
                    'start_time' => $slotStart->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'start_datetime' => $slotStart->toDateTimeString(),
                    'end_datetime' => $slotEnd->toDateTimeString(),
                ];
            }
        }

        return $slots;
    }

    public function getDosesSchedule()
    {
        $doses = DoseItemRecord::where([
            'consultation_id' => $this->id,
        ])->get();
        $dates = [];
        foreach ($doses as $dose) {
            //check if in arrat and continue
            if (in_array($dose->due_date, $dates)) {
                continue;
            }
            $dates[] = $dose->due_date;
        }
        $data = [];
        foreach ($dates as $key => $date) {
            $recs = DoseItemRecord::where([
                'consultation_id' => $this->id,
                'due_date' => $date,
            ])->get();
            $d['date'] = $date;
            $d['day'] = Carbon::parse($date)->format('l');
            $morning = [];
            $afternoon = [];
            $evening = [];
            $night = [];

            foreach ($recs as $rec) {
                if ($rec->time_value == 1) {
                    $morning[] = $rec;
                } else if ($rec->time_value == 2) {
                    $afternoon[] = $rec;
                } else if ($rec->time_value == 3) {
                    $evening[] = $rec;
                } else if ($rec->time_value == 4) {
                    $night[] = $rec;
                }
            }
            $d['morning'] = $morning;
            $d['afternoon'] = $afternoon;
            $d['evening'] = $evening;
            $d['night'] = $night;
            $data[] = $d;
        }


        /* 
          "id" => 1
          "created_at" => "2024-09-17 09:44:55"
          "updated_at" => "2024-09-17 09:44:55"
          "consultation_id" => 6
          "medicine" => "Asprin"
          "quantity" => 2
          "units" => "Mills"
          "times_per_day" => 3
          "number_of_days" => 2
          "status" => "Not taken"
          "remarks" => null
          "due_date" => "2024-09-17"
          "date_submitted" => null
          "dose_item_id" => 3
          "time_name" => "Morning"
          "time_value" => "1"
  0 => "2024-09-17"
  1 => "2024-09-18"
  2 => "2024-09-20"
  3 => "2024-09-23"
  4 => "2024-09-27"
  5 => "2024-10-02"
  6 => "2024-10-08"
  7 => "2024-10-15"
  8 => "2024-10-23"
  9 => "2024-11-01"
]
*/
        return $data;
    }

    /**
     * Additional appointment-specific helper methods
     */

    /**
     * Check if appointment is overdue
     */
    public function isOverdue()
    {
        return $this->appointment_date < now() && 
               in_array($this->status, ['scheduled', 'confirmed']);
    }

    /**
     * Check if appointment is upcoming
     */
    public function isUpcoming()
    {
        return $this->appointment_date > now() && 
               in_array($this->status, ['scheduled', 'confirmed']);
    }

    /**
     * Check if appointment is today
     */
    public function isToday()
    {
        return $this->appointment_date && 
               $this->appointment_date->isToday();
    }

    /**
     * Get appointment duration in readable format
     */
    public function getDurationText()
    {
        if ($this->duration_minutes < 60) {
            return $this->duration_minutes . ' minutes';
        } else {
            $hours = floor($this->duration_minutes / 60);
            $minutes = $this->duration_minutes % 60;
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . 
                   ($minutes > 0 ? ' ' . $minutes . ' minutes' : '');
        }
    }

    /**
     * Get appointment time range as formatted string
     */
    public function getTimeRange()
    {
        if (!$this->appointment_date) {
            return null;
        }
        
        $start = $this->appointment_date;
        $end = $this->appointment_end_date ?? $start->copy()->addMinutes($this->duration_minutes);
        
        return $start->format('H:i') . ' - ' . $end->format('H:i');
    }

    /**
     * Check if appointment needs confirmation
     */
    public function needsConfirmation()
    {
        return $this->confirmation_required && !$this->confirmed_at;
    }

    /**
     * Confirm the appointment
     */
    public function confirmAppointment($userId = null)
    {
        $this->update([
            'confirmed_at' => now(),
            'confirmed_by' => $userId,
            'status' => 'confirmed'
        ]);
    }

    /**
     * Check in the patient for the appointment
     */
    public function checkIn()
    {
        $this->update([
            'checked_in_at' => now(),
            'status' => 'in_progress'
        ]);
    }

    /**
     * Start the appointment/consultation
     */
    public function startAppointment()
    {
        $this->update([
            'started_at' => now(),
            'status' => 'in_progress'
        ]);
    }

    /**
     * Complete the appointment/consultation
     */
    public function completeAppointment()
    {
        $this->update([
            'completed_at' => now(),
            'status' => 'completed'
        ]);
    }

    /**
     * Cancel the appointment
     */
    public function cancelAppointment($reason = null, $userId = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
            'cancelled_by' => $userId
        ]);
    }

    /**
     * Reschedule the appointment
     */
    public function rescheduleAppointment($newDate, $newEndDate = null)
    {
        $this->update([
            'appointment_date' => $newDate,
            'appointment_end_date' => $newEndDate,
            'status' => 'rescheduled'
        ]);
    }

    /**
     * Send reminder for the appointment
     */
    public function sendReminder($type = 'both')
    {
        $updates = ['reminder_sent_at' => now()];
        
        if (in_array($type, ['sms', 'both'])) {
            $updates['sms_reminder_sent'] = true;
        }
        
        if (in_array($type, ['email', 'both'])) {
            $updates['email_reminder_sent'] = true;
        }
        
        $this->update($updates);
    }
}

<?php

namespace App\Models;

use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class Consultation extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $loggedInUser = Auth::user();
            
            \Log::info('🔵 Consultation::creating - Starting consultation creation', [
                'is_new_patient' => $model->is_new_patient ?? 'not set',
                'patient_id' => $model->patient_id ?? 'not set',
                'new_patient_first_name' => $model->new_patient_first_name ?? 'not set',
                'new_patient_last_name' => $model->new_patient_last_name ?? 'not set',
                'logged_user_id' => $loggedInUser ? $loggedInUser->id : 'null',
                'logged_user_enterprise_id' => $loggedInUser ? $loggedInUser->enterprise_id : 'null'
            ]);
            
            if ($loggedInUser == null) {
                \Log::error('❌ User not authenticated during consultation creation');
                throw new \Exception('User not authenticated. Cannot create consultation.');
            }
            
            $model->company_id = $loggedInUser->company_id;
            $model->enterprise_id = $loggedInUser->enterprise_id; // Set enterprise_id FIRST
            
            if ($model->receptionist_id == null) {
                $model->receptionist_id = $loggedInUser->id; 
            }
            if ($model->specialist_id == null) {  
                $model->specialist_id = $loggedInUser->id;
            }

            // Handle new patient creation BEFORE do_prepare if is_new_patient is true
            if ($model->is_new_patient === true || $model->is_new_patient === 1 || $model->is_new_patient === '1') {
                \Log::info('🟢 New patient flag detected - Creating new patient first');
                $model = Consultation::handle_new_patient_creation($model);
                \Log::info('✅ Patient created successfully', ['patient_id' => $model->patient_id]);
            }
            
            // Now prepare consultation details
            $model = Consultation::do_prepare($model);
            
            \Log::info('✅ Consultation prepared successfully', [
                'consultation_number' => $model->consultation_number,
                'patient_id' => $model->patient_id,
                'enterprise_id' => $model->enterprise_id
            ]);
            
            // Remove temporary fields that don't exist in database
            // These are only used during the creation process
            unset($model->patient_created_successfully);
            unset($model->is_new_patient);
            unset($model->new_patient_first_name);
            unset($model->new_patient_last_name);
            unset($model->new_patient_email);
            unset($model->new_patient_phone);
            unset($model->new_patient_address);
            unset($model->new_patient_date_of_birth);
            unset($model->new_patient_gender);
            
            \Log::info('🔵 Removed temporary new patient fields before save');
        });

        static::updating(function ($model) {
            $model = Consultation::do_prepare($model);
        });

        //deleting
        static::deleting(function ($model) {
            $medical_services = $model->medical_services;
            foreach ($medical_services as $medical_service) {
                $medical_service->delete();
            }
            $billing_items = $model->billing_items;
            foreach ($billing_items as $billing_item) {
                $billing_item->delete();
            }
            $payment_records = $model->payment_records;
            foreach ($payment_records as $payment_record) {
                $payment_record->delete();
            }
        });
    }

    static function do_prepare($model)
    {
        // If this is a new patient consultation, skip patient validation temporarily
        if ($model->is_new_patient && !isset($model->patient_created_successfully)) {
            // Patient will be created in handle_new_patient_creation
            // Set basic consultation details first
            $loggedUser = Auth::user();
            if ($loggedUser != null) {
                $model->company_id = $loggedUser->company_id;
                if ($model->receptionist_id == null) {
                    $model->receptionist_id = $loggedUser->id;
                }
            }

            if ($model->consultation_number == null || strlen($model->consultation_number) < 2) {
                $model->consultation_number = Consultation::generate_consultation_number();
            }

            return $model;
        }

        // Handle patient validation and data loading
        // For new patients: patient_id is set after creation in handle_new_patient_creation
        // For existing patients: patient_id must exist and be valid
        
        // Normalize empty string to null
        if ($model->patient_id === '' || $model->patient_id === 'null') {
            $model->patient_id = null;
            \Log::info('🔵 Normalized empty/string patient_id to null');
        }
        
        if ($model->patient_id !== null && $model->patient_id !== '') {
            // We have a valid patient_id - validate and load patient data
            \Log::info('🔵 Loading patient data for patient_id: ' . $model->patient_id);
            
            $patient = User::find($model->patient_id);
            if ($patient == null) {
                \Log::error('❌ Patient not found with id: ' . $model->patient_id);
                throw new \Exception('Patient not found. Please select a valid patient.');
            }

            // Update consultation with patient information
            $patient->company_id = $model->company_id;
            $model->patient_name = $patient->name;
            $model->patient_contact = $patient->phone_number_1;
            $model->contact_address = $patient->current_address;
            
            \Log::info('✅ Patient data loaded successfully', ['patient_name' => $model->patient_name]);
        } elseif (!$model->is_new_patient) {
            // Existing patient selected but no valid patient_id provided
            \Log::error('❌ No patient_id provided for existing patient consultation');
            throw new \Exception('Patient selection is required. Please select a valid patient.');
        }
        // If is_new_patient=true and no patient_id yet, that's OK - it will be set by handle_new_patient_creation()

        $receptionist = User::find($model->receptionist_id);
        $loggedUser = Auth::user();

        if ($receptionist == null) {
            $model->receptionist_id = $loggedUser->id;
        }

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

    //static function to handle new patient creation
    static function handle_new_patient_creation($model)
    {
        \Log::info('🔵 handle_new_patient_creation - Starting new patient creation', [
            'new_patient_first_name' => $model->new_patient_first_name ?? 'not set',
            'new_patient_last_name' => $model->new_patient_last_name ?? 'not set',
            'new_patient_email' => $model->new_patient_email ?? 'not set',
            'new_patient_phone' => $model->new_patient_phone ?? 'not set',
        ]);
        
        // Validate required new patient fields
        if (empty($model->new_patient_first_name) || empty($model->new_patient_last_name)) {
            \Log::error('❌ Missing required patient name fields', [
                'first_name' => $model->new_patient_first_name ?? 'missing',
                'last_name' => $model->new_patient_last_name ?? 'missing'
            ]);
            throw new \Exception('Patient first name and last name are required for new patients.');
        }

        if (empty($model->new_patient_phone) && empty($model->new_patient_email)) {
            \Log::error('❌ Missing contact information (phone and email both empty)');
            throw new \Exception('Either phone number or email is required for new patients.');
        }

        // Check if patient with same email already exists
        if (!empty($model->new_patient_email)) {
            $existingPatient = User::where('email', $model->new_patient_email)->first();
            if ($existingPatient) {
                \Log::warning('⚠️ Patient with email already exists', ['email' => $model->new_patient_email]);
                throw new \Exception('A patient with this email already exists. Please use existing patient option.');
            }
        }

        // Check if patient with same phone already exists
        if (!empty($model->new_patient_phone)) {
            $existingPatient = User::where('phone_number_1', $model->new_patient_phone)->first();
            if ($existingPatient) {
                \Log::warning('⚠️ Patient with phone number already exists', ['phone' => $model->new_patient_phone]);
                throw new \Exception('A patient with this phone number already exists. Please use existing patient option.');
            }
        }

        try {
            // Get logged user for company and enterprise assignment
            $loggedUser = Auth::user();
            if (!$loggedUser) {
                \Log::error('❌ No authenticated user found during patient creation');
                throw new \Exception('Authentication required to create new patient.');
            }
            
            $companyId = $loggedUser->company_id;
            $enterpriseId = $loggedUser->enterprise_id;
            
            \Log::info('🔵 Creating patient with context', [
                'company_id' => $companyId,
                'enterprise_id' => $enterpriseId,
                'logged_user_id' => $loggedUser->id
            ]);

            // Validate enterprise_id is present
            if (empty($enterpriseId)) {
                \Log::error('❌ enterprise_id is missing from logged user', ['user_id' => $loggedUser->id]);
                throw new \Exception('Enterprise context is required to create new patient.');
            }

            // Create new patient
            $newPatient = new User();
            $newPatient->first_name = trim($model->new_patient_first_name);
            $newPatient->last_name = trim($model->new_patient_last_name);
            $newPatient->name = trim($model->new_patient_first_name . ' ' . $model->new_patient_last_name);
            $newPatient->email = !empty($model->new_patient_email) ? trim($model->new_patient_email) : null;
            $newPatient->phone_number_1 = !empty($model->new_patient_phone) ? trim($model->new_patient_phone) : null;
            $newPatient->current_address = !empty($model->new_patient_address) ? trim($model->new_patient_address) : null;
            $newPatient->date_of_birth = !empty($model->new_patient_date_of_birth) ? $model->new_patient_date_of_birth : null;
            $newPatient->sex = !empty($model->new_patient_gender) ? $model->new_patient_gender : null;
            $newPatient->company_id = $companyId;
            
            // CRITICAL: Set enterprise_id explicitly BEFORE save to ensure proper tenant isolation
            $newPatient->enterprise_id = $enterpriseId;

            // Set patient-specific defaults
            $newPatient->user_type = 'Patient';
            $newPatient->status = 1; // 1 = Active, 0 = Inactive (integer field)
            $newPatient->card_status = 'Inactive'; // Set to Inactive initially for security
            
            // Set username - prefer email if available, otherwise use phone
            if (!empty($newPatient->email)) {
                $newPatient->username = $newPatient->email;
            } else {
                $newPatient->username = $newPatient->phone_number_1;
            }
            
            // Set default password
            $newPatient->password = bcrypt('patient123');
            
            \Log::info('🔵 Attempting to save new patient', [
                'name' => $newPatient->name,
                'email' => $newPatient->email,
                'phone' => $newPatient->phone_number_1,
                'enterprise_id' => $newPatient->enterprise_id,
                'company_id' => $newPatient->company_id
            ]);

            // Save the new patient
            $newPatient->save();
            
            \Log::info('✅ New patient saved successfully', [
                'patient_id' => $newPatient->id,
                'name' => $newPatient->name,
                'enterprise_id' => $newPatient->enterprise_id
            ]);

            // Update consultation with new patient ID and patient info
            $model->patient_id = $newPatient->id;
            $model->patient_name = $newPatient->name;
            $model->patient_contact = $newPatient->phone_number_1;
            $model->contact_address = $newPatient->current_address;
            $model->patient_created_successfully = true; // Flag to prevent recursion in do_prepare

            // Clear temporary new patient fields after successful creation
            $model->new_patient_first_name = null;
            $model->new_patient_last_name = null;
            $model->new_patient_email = null;
            $model->new_patient_phone = null;
            $model->new_patient_address = null;
            $model->new_patient_date_of_birth = null;
            $model->new_patient_gender = null;
            
            \Log::info('✅ Consultation model updated with new patient_id', ['patient_id' => $model->patient_id]);

            return $model;
        } catch (\Exception $e) {
            \Log::error('❌ Exception during patient creation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Failed to create new patient: ' . $e->getMessage());
        }
    }

    //has many MedicalService
    public function medical_services()
    {
        return $this->hasMany(MedicalService::class);
    }

    //has many MedicalService
    public function dose_items()
    {
        return $this->hasMany(DoseItem::class);
    }

    //has many BillingItem
    public function billing_items()
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
        $medical_services = $this->medical_services;
        foreach ($medical_services as $key => $val) {
            if (count($val->medical_service_items) < 1) {
                continue;
            }
            //loop through $val->medical_service_items) 
            foreach ($val->medical_service_items as $service_item) {
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

        $medical_services = $this->medical_services;
        $subtotal = 0;
        foreach ($medical_services as $medical_service) {
            $isFirst = true;
            $medical_service->remarks = null;
            $medical_service->total_price = 0;

            foreach ($medical_service->medical_service_items as $medical_service_item) {
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
        foreach ($this->billing_items as $billing_item) {
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
        $medical_services = $this->medical_services;
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
        $medical_services = $this->medical_services;
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
    public function payment_records()
    {
        return $this->hasMany(PaymentRecord::class);
    }

    public static function get_payble_consultations()
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
    public function drug_item_records()
    {
        return $this->hasMany(DoseItemRecord::class);
    }


    //appends for services_text
    protected $appends = ['services_text', 'name_text'];

    public function get_doses_schedule()
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
}

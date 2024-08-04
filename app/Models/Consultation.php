<?php

namespace App\Models;

use Barryvdh\DomPDF\PDF;
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
            $model = Consultation::do_prepare($model);
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
        $patient = User::find($model->patient_id);
        if ($patient == null) {
            throw new \Exception('Patient not found');
        }
        $patient->company_id = $model->company_id;
        $receptionist = User::find($model->receptionist_id);
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
    public function medical_services()
    {
        return $this->hasMany(MedicalService::class);
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


    public function process_invoice()
    {
        if ($this->main_status != 'Ongoing') {
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

    //appends for services_text
    protected $appends = ['services_text', 'name_text'];
}

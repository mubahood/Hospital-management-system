<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
            throw new \Exception('You are not logged in.');
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
}

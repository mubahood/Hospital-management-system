<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientRecord extends Model
{
    use HasFactory;

    //for patient
    public function patient_user()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }


    //patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    //admmistrator
    public function administrator()
    {
        return $this->belongsTo(Administrator::class);
    }
}

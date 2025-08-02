<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientRecord extends Model
{
    use HasFactory, EnterpriseScopeTrait, StandardBootTrait;

    protected $fillable = [
        'enterprise_id',
        'patient_id',
        'administrator_id',
        'record_date',
        'symptoms',
        'diagnosis',
        'treatment',
        'notes'
    ];

    //for patient
    public function patientUser()
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

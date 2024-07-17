<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalService extends Model
{
    use HasFactory;

    //belongs to Consultation
    /* 
        create fillables for theses
        	
    */
    protected $fillable = [
        'created_at',
        'updated_at',
        'consultation_id',
        'receptionist_id',
        'patient_id',
        'assigned_to_id',
        'type',
        'status',
        'remarks',
        'instruction',
        'specialist_outcome',
        'file',
        'description',
        'total_price',
        'quantity',
        'unit_price',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    //has many MedicalServiceItem
    public function medical_service_items()
    {
        return $this->hasMany(MedicalServiceItem::class);
    }
}

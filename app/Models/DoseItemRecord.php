<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoseItemRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'dose_item_id',
        'patient_id', 
        'administered_by',
        'administration_date',
        'administration_time',
        'quantity_administered',
        'status',
        'remarks',
        'next_dose_date'
    ];
}

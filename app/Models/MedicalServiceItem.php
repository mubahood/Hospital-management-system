<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalServiceItem extends Model
{
    use HasFactory;
    //create fillables for 	id	created_at	updated_at	medical_service_id	stock_item_id	description	file	quantity	unit_price	total_price	remarks	

    protected $fillable = [
        'id',
        'created_at',
        'updated_at',
        'medical_service_id',
        'stock_item_id',
        'description',
        'file',
        'quantity',
        'unit_price',
        'total_price',
        'remarks',
    ];

    //belongs to MedicalService
    public function medical_service()
    {
        return $this->belongsTo(MedicalService::class);
    }
}

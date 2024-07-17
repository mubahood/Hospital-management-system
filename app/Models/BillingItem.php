<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'description',
        'price',
        'consultation_id'
    ];

    //belongs to Consultation
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}

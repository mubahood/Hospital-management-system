<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentRecordItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tooth',
        'finding',
        'treatment',
        'status',
    ];

    //treatment record relationship
    public function treatment_record()
    {
        return $this->belongsTo(TreatmentRecord::class);
    }
}

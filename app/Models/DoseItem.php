<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_at',
        'updated_at',
        'consultation_id',
        'medicine',
        'quantity',
        'units',
        'times_per_day',
        'number_of_days',
        'is_processed',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($doseItemRecord) {
            $doseItemRecord->doseItemRecords()->delete();
        });
    }

    //has many
    public function doseItemRecords()
    {
        return $this->hasMany(DoseItemRecord::class);
    }
}

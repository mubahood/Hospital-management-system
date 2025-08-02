<?php

namespace App\Models;

use App\Traits\StandardBootTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoseItem extends Model
{
    use HasFactory, StandardBootTrait;

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

    protected static function boot(): void
    {
        parent::boot();
        
        // Call standardized boot methods
        static::bootStandardBootTrait();
    }

    /**
     * Handle pre-deletion logic - called by StandardBootTrait
     */
    protected static function onDeleting($model): void
    {
        $model->doseItemRecords()->delete();
    }

    //has many
    public function doseItemRecords()
    {
        return $this->hasMany(DoseItemRecord::class);
    }
}

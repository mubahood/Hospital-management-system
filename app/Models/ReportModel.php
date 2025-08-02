<?php

namespace App\Models;

use App\Traits\StandardBootTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ReportModel extends Model
{
    use HasFactory, StandardBootTrait;

    protected $fillable = [
        'company_id',
        'user_id',
        'project_id',
        'department_id',
        'type',
        'title',
        'date_rage_type',
        'date_range',
        'generated',
        'start_date',
        'end_date',
        'pdf_file',
        'other_id'
    ];

    protected static function boot(): void
    {
        parent::boot();
        
        // Call standardized boot methods
        static::bootStandardBootTrait();
    }

    /**
     * Handle model creation logic - called by StandardBootTrait
     */
    protected static function onCreating($model): void
    {
        $model->company_id = Auth::user()->company_id;
    }

    //belongs to company
    public function company()
    {
        return $this->belongsTo(Company::class);
    } 
}

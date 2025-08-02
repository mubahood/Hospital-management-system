<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory, EnterpriseScopeTrait, StandardBootTrait;

    protected $fillable = [
        'enterprise_id',
        'name',
        'description',
        'head_of_department_id',
        'company_id'
    ];

    public function headOfDepartment()
    {
        return $this->belongsTo(Administrator::class, 'head_of_department_id');
    }
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

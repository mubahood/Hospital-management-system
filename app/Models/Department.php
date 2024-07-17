<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    public function head_of_department()
    {
        return $this->belongsTo(Administrator::class, 'head_of_department_id');
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        static::created(function ($m) {
            $owner = Administrator::find($m->administrator_id);
            if ($owner == null) {
                throw new \Exception("Owner not found");
            }
            $owner->company_id = $m->id;
            $owner->save();
        });
        static::updated(function ($m) {
            $owner = Administrator::find($m->administrator_id);
            if ($owner == null) {
                throw new \Exception("Owner not found");
            }
            $owner->company_id = $m->id;
            $owner->save();
        });
    }

    //employees
    public function employees()
    {
        return $this->hasMany(Administrator::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    //boot on deleting throw exception
    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($model) {
            throw new \Exception('This model cannot be deleted.');
        });
    }
}

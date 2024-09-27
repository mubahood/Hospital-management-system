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

    //array of id and name_text 
    public static function get_list()
    {
        $services = Service::all();
        $list = [];
        foreach ($services as $service) {
            $list[$service->id] = $service->name;
        }
        return $list;
    }

    //name_text (with price) GETTER
    public function getNameTextAttribute()
    {
        return $this->name . ' (UGX Service' . number_format($this->price, 0) . ')';
    }
    //appends name_text
    protected $appends = ['name_text'];
}

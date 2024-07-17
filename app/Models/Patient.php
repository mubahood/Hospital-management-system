<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;


    //list of this model to array, id and name
    public static function toSelectArray()
    {
        $patients = Patient::all();
        $patients_array = [];
        foreach ($patients as $patient) {
            $patients_array[$patient->id] = $patient->first_name . ' ' . $patient->last_name . " - " . $patient->phone_number_1;
        }
        return $patients_array;
    }

    //administrator_id relation
    public function administrator()
    {
        return $this->belongsTo(Administrator::class);
    }
    //appends full name
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    protected $appends = ['full_name'];
}

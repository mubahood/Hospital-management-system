<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentRecord extends Model
{
    use HasFactory;

    //create function to array for select
    public static function toSelectArray()
    {
        $items = TreatmentRecord::all();
        $ret = [];
        foreach ($items as $item) {
            if ($item->patient == null) {
                $item->delete();
            }
            $ret[$item->id] = "#" . $item->id . " " . $item->patient->first_name . " " . $item->patient->last_name . ", " . $item->patient->phone_number . ' - ' . Utils::my_date($item->created_at);
        }
        return $ret;
    }

    //for patient
    public function patient_user()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    //setter and getter for mulpiple photos
    public function setPhotosAttribute($value)
    {
        $this->attributes['photos'] = json_encode($value);
    }
    //setter and getter for mulpiple photos
    public function getPhotosAttribute($value)
    {
        return $this->attributes['photos'] = json_decode($value);
    }
    //patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    //admmistrator
    public function administrator()
    {
        return $this->belongsTo(Administrator::class);
    }
    public function items()
    {
        return $this->hasMany(TreatmentRecordItem::class);
    }

    //name attribute
    public function getNameAttribute()
    {
        return $this->patient_user->name;
    }
}

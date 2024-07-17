<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;

    //department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    //lead
    public function lead()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    //getter for files attribute
    public function getFilesAttribute($value)
    {
        return json_decode($value);
    }
    //setter for files attribute
    public function setFilesAttribute($value)
    {
        $this->attributes['files'] = json_encode($value);
    }

    //getter for photos attribute
    public function getPhotosAttribute($value)
    {
        return json_decode($value);
    }
    //setter for photos attribute
    public function setPhotosAttribute($value)
    {
        $this->attributes['photos'] = json_encode($value);
    }

    //getter for members attribute
    public function getMembersAttribute($value)
    {
        return json_decode($value);
    }
    //setter for members attribute
    public function setMembersAttribute($value)
    {
        $this->attributes['members'] = json_encode($value);
    }
}

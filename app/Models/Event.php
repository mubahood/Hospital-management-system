<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $m->event_conducted = 'Pending';
            return Event::my_update($m);
        });
        self::updating(function ($m) {
            return Event::my_update($m);
        });
    }

    public function get_participants()
    {
        $users = [];
        $users[] = $this->user;
        $users[] = Administrator::where('id', $this->administrator_id)->first();
        $users = array_merge($users, Administrator::whereIn('id', $this->users_to_notify)->get()->all());
        return $users;
    }

    public function get_participants_names()
    {
        $users = $this->get_participants();
        $names = [];
        foreach ($users as $u) {
            if ($u == null) {
                continue;
            }
            $names[] = $u->name;
        }
        return implode(', ', $names);
    }
    public static function my_update($m)
    {
        if ($m->reminder_state == 'On') {
            $m->reminder_date = Carbon::parse($m->event_date)->subDays((int) $m->remind_beofre_days);
        }
        return $m;
    }

    public function user()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    public function setUsersToNotifyAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['users_to_notify'] = implode(',', $value);
        }
    }


    public function getUsersToNotifyAttribute($value)
    {
        return explode(',', $value);
    }

    //getter for images like that one above
    public function getImagesAttribute($value)
    {
        $images = [];
        if (strlen($value) > 2) {
            $images = explode(',', $value);
        }
        return $images;
    }

    //setter for images like that one above
    public function setImagesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['images'] = implode(',', $value);
        }
    }

    //do the same for files
    public function getFilesAttribute($value)
    {
        $files = [];
        if (strlen($value) > 2) {
            $files = explode(',', $value);
        }
        return $files;
    }

    public function setFilesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['files'] = implode(',', $value);
        }
    }
}

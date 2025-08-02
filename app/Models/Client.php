<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id',
        'name',
        'email',
        'phone',
        'address',
        'contact_person',
        'client_type',
        'status',
        'notes'
    ];
}

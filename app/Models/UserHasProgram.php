<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHasProgram extends Model
{
    protected $table = 'user_has_program';
    protected $fillable = [
        'program_award',
        'program_name',
        'program_year',
        'program_compas',
        'user_id',
        'campus_id',
    ];

     
    public function campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }
     

    use HasFactory;
}

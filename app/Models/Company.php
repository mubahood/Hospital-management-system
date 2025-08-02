<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory, EnterpriseScopeTrait, StandardBootTrait;

    protected $fillable = [
        'enterprise_id',
        'name',
        'short_name',
        'details',
        'logo',
        'phone_number',
        'phone_number_2',
        'p_o_box',
        'email',
        'address',
        'administrator_id'
    ];

    protected static function boot(): void
    {
        parent::boot();
        
        // Call standardized boot methods
        static::bootStandardBootTrait();
    }

    /**
     * Handle post-creation logic - called by StandardBootTrait
     */
    protected static function onCreated($model): void
    {
        $owner = Administrator::find($model->administrator_id);
        if ($owner == null) {
            throw new \Exception("Owner not found");
        }
        $owner->company_id = $model->id;
        $owner->save();
    }

    /**
     * Handle post-update logic - called by StandardBootTrait
     */
    protected static function onUpdated($model): void
    {
        $owner = Administrator::find($model->administrator_id);
        if ($owner == null) {
            throw new \Exception("Owner not found");
        }
        $owner->company_id = $model->id;
        $owner->save();
    }

    //employees
    public function employees()
    {
        return $this->hasMany(Administrator::class);
    }
}

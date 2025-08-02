<?php

namespace App\Models;

use App\Traits\StandardBootTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory, StandardBootTrait;
    
    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'status',
        'duration',
        'department_id',
        'enterprise_id'
    ];
    
    protected static function boot(): void
    {
        parent::boot();
        
        // Call standardized boot methods
        static::bootStandardBootTrait();
    }

    /**
     * Handle pre-deletion logic - called by StandardBootTrait
     */
    protected static function onDeleting($model): void
    {
        throw new \Exception('This model cannot be deleted.');
    }

    //array of id and name_text 
    public static function getList()
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

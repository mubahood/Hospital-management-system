<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockItemCategory extends Model
{
    use HasFactory;
    //boot disable deleting
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($model) {
            throw new \Exception('This model cannot be deleted.');
        });
    }

    //get dopdown options for select
    public static function getDropdownOptions()
    {
        $options = [];
        $models = StockItemCategory::where([])
            ->orderBy('name', 'asc')
            ->get();
        foreach ($models as $model) {
            $options[$model->id] = $model->name . ' (' . $model->measuring_unit . ')';
        }
        return $options;
    }

    //update_quantities
    public function update_quantities()
    {
    }
}

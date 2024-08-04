<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($model) {
            throw new \Exception('This model cannot be deleted.');
        });

        //boot
        static::creating(function ($model) {
            $model = StockItem::preparing($model);
            $model->current_quantity = $model->original_quantity;
        });

        //updating
        static::updating(function ($model) {
            $model = StockItem::preparing($model);
        });

        //updated
        static::updated(function ($model) {
            $cat = StockItemCategory::find($model->stock_item_category_id);
            if ($cat != null) {
                $cat->update_quantities();
            }
        });

        //created
        static::created(function ($model) {
            $cat = StockItemCategory::find($model->stock_item_category_id);
            if ($cat != null) {
                $cat->update_quantities();
            }
        });
    }

    //has many StockOutRecord
    public function stock_out_records()
    {
        return $this->hasMany(StockOutRecord::class);
    }


    public function update_quantities()
    {
        if ($this->type != 'Product') {
            return;
        }
        $toal_quantities_used = StockOutRecord::where('stock_item_id', $this->id)->sum('quantity');
        $this->current_quantity = $this->original_quantity - $toal_quantities_used;
        $this->current_stock_value = $this->current_quantity * $this->sale_price;
        $this->save();
    }

    public static function preparing($model)
    {
        $category = StockItemCategory::find($model->stock_item_category_id);
        if ($category == null) {
            throw new \Exception("Category not found");
        }
        $model->measuring_unit = $category->measuring_unit;
        $model->stock_item_category_id = $category->id;
        return $model;
    }

    //get dopdown options for select
    public static function getDropdownOptions()
    {
        $options = [];
        $models = StockItem::where([])
            ->orderBy('name', 'asc')
            ->get();
        foreach ($models as $model) {
            if ($model->current_quantity <= 0) {
                continue;
            }
            $options[$model->id] = $model->name . ' (' . number_format($model->current_quantity) . " " . $model->measuring_unit . ')';
        }
        return $options;
    }

    //category
    public function category()
    {
        return $this->belongsTo(StockItemCategory::class, 'stock_item_category_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOutRecord extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($model) {
        });

        //created
        static::creating(function ($model) {
            $stockItem = StockItem::find($model->stock_item_id); 
            if ($stockItem == null) {
                throw new \Exception("Category not found");
            }
            $model->stock_item_category_id = $stockItem->stock_item_category_id;
        });

        //updated
        static::updating(function ($model) {
            $stockItem = StockItem::find($model->stock_item_id);
            if ($stockItem == null) {
                throw new \Exception("Category not found");
            }
            $model->stock_item_category_id = $stockItem->stock_item_category_id;
        });
        //updated finalizer

        //created finalizer
        static::created(function ($model) {
            self::finalizer($model);
        });

        //updated finalizer
        static::updated(function ($model) {
            self::finalizer($model);
        });
    }


    //finalizer function
    public static function finalizer($model)
    {
        $stockItem = StockItem::find($model->stock_item_id);
        if ($stockItem == null) {
            throw new \Exception("Category not found");
        }
        $stockItem->update_quantities();
    }

    //belongs to MedicalService
    public function medical_service()
    {
        return $this->belongsTo(MedicalService::class);
    }

    //belongs to stock_item_id
    public function stock_item()
    {
        return $this->belongsTo(StockItem::class);
    } 
}

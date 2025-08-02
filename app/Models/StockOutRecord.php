<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOutRecord extends Model
{
    use HasFactory, EnterpriseScopeTrait, StandardBootTrait;

    protected $fillable = [
        'enterprise_id',
        'stock_item_id',
        'stock_item_category_id',
        'medical_service_id',
        'quantity_used',
        'unit_price',
        'total_value',
        'used_by',
        'used_date',
        'remarks',
        'created_by'
    ];

    //boot
    protected static function boot(): void
    {
        parent::boot();
        
        // Call standardized boot methods
        static::bootStandardBootTrait();
    }

    /**
     * Handle model creation logic - called by StandardBootTrait
     */
    protected static function onCreating($model): void
    {
        $stockItem = StockItem::find($model->stock_item_id); 
        if ($stockItem == null) {
            throw new \Exception("Category not found");
        }
        $model->stock_item_category_id = $stockItem->stock_item_category_id;
    }

    /**
     * Handle model updating logic - called by StandardBootTrait
     */
    protected static function onUpdating($model): void
    {
        $stockItem = StockItem::find($model->stock_item_id);
        if ($stockItem == null) {
            throw new \Exception("Category not found");
        }
        $model->stock_item_category_id = $stockItem->stock_item_category_id;
    }

    /**
     * Handle post-creation logic - called by StandardBootTrait
     */
    protected static function onCreated($model): void
    {
        self::finalizer($model);
    }

    /**
     * Handle post-update logic - called by StandardBootTrait
     */
    protected static function onUpdated($model): void
    {
        self::finalizer($model);
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
    public function medicalService()
    {
        return $this->belongsTo(MedicalService::class);
    }

    //belongs to stock_item_id
    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    } 
}

<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    use HasFactory, EnterpriseScopeTrait, StandardBootTrait;

    protected $fillable = [
        'enterprise_id',
        'stock_item_category_id',
        'name',
        'description',
        'unit_price',
        'original_quantity',
        'current_quantity',
        'min_quantity',
        'expiry_date',
        'barcode',
        'sku'
    ];

    //boot
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

    /**
     * Handle model creation logic - called by StandardBootTrait
     */
    protected static function onCreating($model): void
    {
        $model = StockItem::preparing($model);
        $model->current_quantity = $model->original_quantity;
    }

    /**
     * Handle model updating logic - called by StandardBootTrait
     */
    protected static function onUpdating($model): void
    {
        $model = StockItem::preparing($model);
    }

    /**
     * Handle post-update logic - called by StandardBootTrait
     */
    protected static function onUpdated($model): void
    {
        $cat = StockItemCategory::find($model->stock_item_category_id);
        if ($cat != null) {
            $cat->update_quantities();
        }
    }

    /**
     * Handle post-creation logic - called by StandardBootTrait
     */
    protected static function onCreated($model): void
    {
        $cat = StockItemCategory::find($model->stock_item_category_id);
        if ($cat != null) {
            $cat->update_quantities();
        }
    }

    //has many StockOutRecord
    public function stockOutRecords()
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

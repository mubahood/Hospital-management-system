<?php

namespace App\Models;

use App\Traits\StandardBootTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockItemCategory extends Model
{
    use HasFactory, StandardBootTrait;
    
    protected $fillable = [
        'enterprise_id',
        'name',
        'description',
        'measuring_unit',
        'current_stock_quantity',
        'current_stock_value',
        'recent_stock_value',
        'original_stock_value'
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
        $this->current_stock_quantity = 0;
        $this->current_stock_value = 0;
        $items = StockItem::where([
            'stock_item_category_id' => $this->id,
        ])->get();
        foreach ($items as $item) {
            $this->current_stock_quantity += $item->quantity;
            $this->current_stock_value += $item->quantity * $item->unit_price;
        }
        $this->save();
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockOutRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_out_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(\App\Models\StockItem::class, 'stock_item_id');
            $table->foreignIdFor(\App\Models\StockItemCategory::class, 'stock_item_category_id');
            $table->decimal('unit_price')->nullable();
            $table->decimal('quantity')->nullable();
            $table->decimal('total_price')->nullable();
            $table->decimal('quantity_after')->nullable();
            $table->text('description')->nullable();
            $table->text('details')->nullable();
            $table->string('measuring_unit')->nullable();
            $table->string('due_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_out_records');
    }
}

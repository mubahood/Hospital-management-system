<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(\App\Models\StockItemCategory::class, 'stock_item_category_id');
            $table->text('name')->nullable();
            $table->decimal('original_quantity')->nullable()->default(0);
            $table->decimal('current_quantity')->nullable()->default(0);
            $table->decimal('current_stock_value')->nullable()->default(0);
            $table->text('description')->nullable();
            $table->decimal('current_stock_quantity')->nullable()->default(0);
            $table->decimal('reorder_level')->nullable()->default(0);
            $table->string('status')->nullable()->default('Active'); 
            $table->string('measuring_unit')->nullable();
            $table->decimal('purchase_price')->nullable()->default(0);
            $table->decimal('sale_price')->nullable()->default(0);
            $table->string('barcode')->nullable();
            $table->string('supplier')->nullable();
            $table->string('supplier_contact')->nullable();
            $table->string('supplier_address')->nullable();
            $table->string('supplier_email')->nullable();
            $table->string('supplier_phone')->nullable();
            $table->string('type')->nullable()->default('Product');
            $table->string('expire_date')->nullable();
            $table->string('manufacture_date')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_items');
    }
}

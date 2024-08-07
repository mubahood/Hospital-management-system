<?php

use App\Models\Company;
use App\Models\Consultation;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignIdFor(User::class, 'card_id')->nullable();
            $table->foreignIdFor(Company::class, 'company_id')->nullable();
            $table->string('type');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('balance', 10, 2)->nullable();
            $table->string('payment_date')->nullable();
            $table->text('payment_remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('card_records');
    }
}

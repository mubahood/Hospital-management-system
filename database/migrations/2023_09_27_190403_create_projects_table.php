<?php

use App\Models\Client;
use App\Models\Company;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Company::class);
            $table->foreignIdFor(Client::class);
            $table->foreignIdFor(Administrator::class);
            $table->text('name')->nullable();
            $table->text('short_name')->nullable();
            $table->text('logo')->nullable();
            $table->text('other_clients')->nullable();
            $table->text('details')->nullable();
            $table->integer('progress')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}

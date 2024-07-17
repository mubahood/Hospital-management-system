<?php

use App\Models\Company;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_models', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Company::class);
            $table->foreignIdFor(User::class)->nullable();
            $table->foreignIdFor(Project::class)->nullable();
            $table->foreignIdFor(Department::class)->nullable();
            $table->string('type')->nullable();
            $table->text('title')->nullable();
            $table->string('date_rage_type')->nullable();
            $table->string('date_range')->nullable();
            $table->string('generated')->nullable()->default('No');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('pdf_file')->nullable();
            $table->integer('other_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_models');
    }
}

<?php

use App\Models\Company;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Company::class);
            $table->foreignIdFor(User::class)->nullable();
            $table->foreignIdFor(Project::class)->nullable();
            $table->foreignIdFor(Department::class)->nullable();
            $table->text('title')->nullable();
            $table->string('type')->nullable()->default('Achievement');
            $table->string('status')->nullable()->default('Pending');
            $table->string('priority')->nullable()->default('Normal');
            $table->text('description')->nullable();
            $table->text('files')->nullable();
            $table->text('photos')->nullable();
            $table->string('due_date')->nullable();
            $table->string('date_completed')->nullable();
            $table->string('date_started')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('targets');
    }
}

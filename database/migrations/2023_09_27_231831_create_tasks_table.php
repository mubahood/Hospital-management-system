<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectSection;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Company::class);
            $table->foreignIdFor(Project::class);
            $table->foreignIdFor(ProjectSection::class);
            $table->foreignIdFor(Administrator::class, 'assigned_to');
            $table->foreignIdFor(Administrator::class, 'created_by');
            $table->foreignIdFor(Administrator::class, 'manager_id');
            $table->text('name')->nullable();
            $table->text('task_description')->nullable();
            $table->dateTime('due_to_date')->nullable();
            $table->string('delegate_submission_status')->nullable();
            $table->text('delegate_submission_remarks')->nullable();
            $table->string('manager_submission_status')->nullable();
            $table->text('manager_submission_remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}

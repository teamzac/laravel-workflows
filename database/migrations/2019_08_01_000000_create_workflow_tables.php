<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use TeamZac\Workflow\Enums\WorkflowStatus;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('workflows.instance_table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->nullableMorphs('workflowable');
            $table->string('workflow')->index();
            $table->enum('status', [
                WorkflowStatus::Queued,
                WorkflowStatus::InProgress,
                WorkflowStatus::Paused,
                WorkflowStatus::Completed,
                WorkflowStatus::Aborted,
            ])->default(WorkflowStatus::Queued);
            $table->text('status_message')->nullable();
            $table->unsignedSmallInteger('current_step')->default(0)->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('workflows.instance_table'));
    }
}

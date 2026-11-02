<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Delegated leadership tasks.
 *
 * A task is assigned by one leader to another and may be scoped to the active
 * ministry or committee. Assignees can update progress; only the assigner (or a
 * holder of `task.manage`) may reassign or delete it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leader_tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ministry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('committee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('todo');
            $table->string('priority', 10)->default('normal');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['assigned_to', 'status'], 'leader_tasks_assignee_status_index');
            $table->index('assigned_by');
            $table->index('due_date');
            $table->index('ministry_id');
            $table->index('committee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leader_tasks');
    }
};

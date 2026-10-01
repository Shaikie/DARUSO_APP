<?php

use App\Enums\ComplaintStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Complaints and their full history.
 *
 * A complaint is private to its creator: visibility is enforced in the policy,
 * not by hiding links, and every state change is journalled in
 * `complaint_history` so the workflow is reconstructable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('category', 50);
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('status', 30)->default(ComplaintStatus::Submitted->value);
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_ministry_id')->nullable()->constrained('ministries')->nullOnDelete();
            $table->foreignId('assigned_leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('creator_id');
            $table->index('assigned_ministry_id');
            $table->index('assigned_leader_id');
            $table->index('category');
        });

        Schema::create('complaint_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('complaint_id');
            $table->index('actor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_history');
        Schema::dropIfExists('complaints');
    }
};

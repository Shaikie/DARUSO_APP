<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Community moderation reports.
 *
 * A report targets either a post or a comment. Content is never deleted on the
 * strength of a report: a moderator reviews it, may hide the content, and the
 * decision is recorded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_comment_id')->nullable()->constrained('post_comments')->cascadeOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 40);
            $table->text('details')->nullable();
            $table->string('status', 20)->default('open');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('moderator_notes')->nullable();
            $table->timestamps();

            $table->index(['post_id', 'status'], 'post_reports_post_status_index');
            $table->index('status');
            $table->index('reporter_id');
        });

        Schema::table('post_comments', function (Blueprint $table): void {
            $table->boolean('is_hidden')->default(false);
            $table->foreignId('hidden_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hidden_at')->nullable();
            $table->text('hidden_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('post_comments', function (Blueprint $table): void {
            $table->dropForeign(['hidden_by']);
            $table->dropColumn(['is_hidden', 'hidden_by', 'hidden_at', 'hidden_reason']);
        });

        Schema::dropIfExists('post_reports');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Periodic ministry activity reports.
 *
 * A report belongs to a ministry and a term, is submitted by its leader and may
 * be reviewed by a Secretary General or administrator. Reports are retained as
 * a historical record even after the term ends.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ministry_activity_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ministry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leadership_term_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->text('summary');
            $table->text('achievements')->nullable();
            $table->text('challenges')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['ministry_id', 'period_start']);
            $table->index('status');
            $table->index('submitted_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ministry_activity_reports');
    }
};

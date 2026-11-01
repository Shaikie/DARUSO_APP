<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Custom entries for the shared calendar.
 *
 * Meetings, events, task deadlines and poll closing dates are aggregated from
 * their own tables; this table only stores entries that do not belong to one of
 * those objects.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 20)->default('general');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_all_day')->default(true);
            $table->string('audience', 20)->default('all');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['start_date', 'end_date']);
            $table->index('type');
            $table->index('audience');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_entries');
    }
};

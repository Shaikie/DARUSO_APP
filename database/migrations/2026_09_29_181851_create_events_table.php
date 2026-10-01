<?php

use App\Enums\EventStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Events and their audience rules.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->date('event_date');
            $table->string('venue');
            $table->string('status', 20)->default(EventStatus::Upcoming->value);
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->timestamps();

            $table->index(['event_date', 'status']);
            $table->index('organizer_id');
        });

        Schema::create('event_audience_rules', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audience_rule_id')->constrained()->cascadeOnDelete();
            $table->primary(['event_id', 'audience_rule_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_audience_rules');
        Schema::dropIfExists('events');
    }
};

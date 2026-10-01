<?php

use App\Enums\MeetingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Meetings and their audience rules.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->date('meeting_date');
            $table->time('meeting_time');
            $table->string('venue');
            $table->string('status', 20)->default(MeetingStatus::Scheduled->value);
            $table->text('agenda')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->timestamps();

            $table->index(['meeting_date', 'status']);
            $table->index('organizer_id');
        });

        Schema::create('meeting_audience_rules', function (Blueprint $table) {
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audience_rule_id')->constrained()->cascadeOnDelete();
            $table->primary(['meeting_id', 'audience_rule_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_audience_rules');
        Schema::dropIfExists('meetings');
    }
};

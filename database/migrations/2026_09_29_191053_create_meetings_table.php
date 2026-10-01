<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->string('status', 20)->default('scheduled');
            $table->text('agenda')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->index('meeting_date');
            $table->index('organizer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};

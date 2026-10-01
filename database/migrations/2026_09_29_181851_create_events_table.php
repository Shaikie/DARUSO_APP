<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->string('status', 20)->default('upcoming');
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->index('event_date');
            $table->index('organizer_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

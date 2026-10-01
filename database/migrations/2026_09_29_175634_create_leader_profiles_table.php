<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marker profile distinguishing leaders from students.
 *
 * Specific positions/ministries/committees live in the organisation tables so
 * that structure remains configurable rather than hard-coded on the user row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leader_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leader_profiles');
    }
};

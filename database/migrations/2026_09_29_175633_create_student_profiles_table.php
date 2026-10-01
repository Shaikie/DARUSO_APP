<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('registration_number')->unique();
            $table->string('college');
            $table->string('school_faculty');
            $table->string('programme');
            $table->unsignedTinyInteger('year_of_study');
            $table->string('hostel')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index('college');
            $table->index('programme');
            $table->index('year_of_study');
            $table->index('hostel');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};

<?php

use App\Enums\StudentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Academic record for a student. Indexed on the columns the audience engine
 * filters by, since those lookups run on every communication read.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('registration_number', 50)->unique();
            $table->string('college');
            $table->string('school_faculty');
            $table->string('programme');
            $table->unsignedTinyInteger('year_of_study');
            $table->string('hostel')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('status', 20)->default(StudentStatus::Active->value);
            $table->timestamps();

            $table->index('college');
            $table->index('school_faculty');
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

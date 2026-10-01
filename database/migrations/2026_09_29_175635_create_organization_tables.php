<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database-driven organisation.
 *
 * Nothing here is hard-coded in application logic: representatives, targeting
 * and ministry scoping are all resolved from these tables, and leadership
 * terms preserve historical context rather than overwriting the present.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leadership_terms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index('is_active');
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('hierarchy_level')->default(0);
            $table->timestamps();

            $table->index('hierarchy_level');
        });

        Schema::create('ministries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('committees', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('leader_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ministry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('leadership_term_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'position_id', 'leadership_term_id'], 'leader_assignments_unique');
            $table->index('ministry_id');
        });

        Schema::create('committee_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('committee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leadership_term_id')->constrained()->cascadeOnDelete();
            $table->string('role_in_committee')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'committee_id', 'leadership_term_id'], 'committee_members_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_members');
        Schema::dropIfExists('leader_assignments');
        Schema::dropIfExists('committees');
        Schema::dropIfExists('ministries');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('leadership_terms');
    }
};

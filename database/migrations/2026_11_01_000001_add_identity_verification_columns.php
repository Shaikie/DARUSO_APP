<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds account standing and student verification to the existing identity
 * tables.
 *
 * Defaults are chosen so every pre-existing row stays usable: current accounts
 * remain `active` and existing academic records remain `verified`. New
 * self-registrations explicitly start `pending`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('account_status', 20)->default('active')->after('password');
            $table->text('status_reason')->nullable()->after('account_status');
            $table->timestamp('status_changed_at')->nullable()->after('status_reason');
            $table->timestamp('last_login_at')->nullable()->after('status_changed_at');

            $table->index('account_status');
        });

        Schema::table('student_profiles', function (Blueprint $table): void {
            $table->string('verification_status', 20)->default('verified')->after('status');
            $table->timestamp('submitted_at')->nullable()->after('verification_status');
            $table->timestamp('verified_at')->nullable()->after('submitted_at');
            $table->foreignId('verified_by')->nullable()->after('verified_at')
                ->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('verified_by');

            $table->index('verification_status');
        });

        Schema::create('student_verification_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40);
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['student_profile_id', 'created_at'], 'verification_history_profile_time_index');
            $table->index('actor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_verification_histories');

        Schema::table('student_profiles', function (Blueprint $table): void {
            $table->dropForeign(['verified_by']);
            $table->dropIndex(['verification_status']);
            $table->dropColumn([
                'verification_status',
                'submitted_at',
                'verified_at',
                'verified_by',
                'rejection_reason',
            ]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['account_status']);
            $table->dropColumn([
                'account_status',
                'status_reason',
                'status_changed_at',
                'last_login_at',
            ]);
        });
    }
};

<?php

use App\Enums\StudentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a review state for student-submitted academic records.
 *
 * Existing active records remain active. New self-service and leader-created
 * records are explicitly created in pending_verification state.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT IF EXISTS student_profiles_status_check');

        $values = array_map(
            static fn (string $value): string => "'{$value}'",
            StudentStatus::values(),
        );

        DB::statement(
            'ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_status_check CHECK (status IN ('.implode(', ', $values).'))'
        );
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("UPDATE student_profiles SET status = 'active' WHERE status = 'pending_verification'");
        DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT IF EXISTS student_profiles_status_check');
        DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_status_check CHECK (status IN ('active', 'suspended', 'graduated', 'expelled'))");
    }
};

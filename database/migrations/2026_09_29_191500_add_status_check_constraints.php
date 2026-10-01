<?php

use App\Enums\ComplaintStatus;
use App\Enums\DocumentVisibility;
use App\Enums\EventStatus;
use App\Enums\MeetingStatus;
use App\Enums\StudentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Native check constraints for the remaining status vocabularies.
 *
 * Kept in one migration so the documented finite states are enforced by the
 * database itself, not only by enum casts. Skipped on SQLite, which does not
 * support ALTER TABLE ... ADD CONSTRAINT.
 */
return new class extends Migration
{
    /**
     * Table, constraint name and status column for each guarded vocabulary.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const TARGETS = [
        'student_profiles' => ['student_profiles_status_check', 'status'],
        'events' => ['events_status_check', 'status'],
        'meetings' => ['meetings_status_check', 'status'],
        'complaints' => ['complaints_status_check', 'status'],
        'documents' => ['documents_visibility_check', 'visibility'],
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->constraints() as [$table, $name, $column, $values]) {
            DB::statement(sprintf(
                'ALTER TABLE %s ADD CONSTRAINT %s CHECK (%s IN (%s))',
                $table,
                $name,
                $column,
                $this->quoteList($values)
            ));
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach (array_reverse($this->constraints(), true) as [, $name, $column, $values]) {
            DB::statement(sprintf(
                'ALTER TABLE %s DROP CONSTRAINT IF EXISTS %s',
                $this->tableFor($column, $name),
                $name
            ));
        }
    }

    /**
     * Resolve each target table to its allowed values.
     *
     * @return array<int, array{0: string, 1: string, 2: string, 3: array<int, string>}>
     */
    private function constraints(): array
    {
        $values = [
            'status' => [
                'student_profiles' => StudentStatus::values(),
                'events' => EventStatus::values(),
                'meetings' => MeetingStatus::values(),
                'complaints' => ComplaintStatus::values(),
            ],
            'visibility' => [
                'documents' => DocumentVisibility::values(),
            ],
        ];

        $constraints = [];

        foreach (self::TARGETS as $table => [$name, $column]) {
            $constraints[] = [$table, $name, $column, $values[$column][$table]];
        }

        return $constraints;
    }

    private function tableFor(string $column, string $constraint): string
    {
        foreach (self::TARGETS as $table => [$name, $targetColumn]) {
            if ($name === $constraint && $targetColumn === $column) {
                return $table;
            }
        }

        return $constraint;
    }

    /**
     * @param  array<int, string>  $values
     */
    private function quoteList(array $values): string
    {
        return implode(', ', array_map(
            static fn (string $value): string => "'{$value}'",
            $values,
        ));
    }
};

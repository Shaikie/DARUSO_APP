<?php

use App\Enums\AnnouncementStatus;
use App\Enums\AudienceType;
use App\Enums\Priority;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Announcements plus their audience rules.
 *
 * Targeting is stored as rules, never as a recipient row per reader, so an
 * "all students" announcement stays a single row regardless of student count.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audience_rules', function (Blueprint $table) {
            $table->id();
            $table->string('audience_type', 32);
            $table->string('audience_value')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['audience_type', 'audience_value']);
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('priority', 10)->default(Priority::Normal->value);
            $table->string('status', 20)->default(AnnouncementStatus::Draft->value);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('requires_approval')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('published_at');
            $table->index('author_id');
            $table->index('priority');
        });

        Schema::create('announcement_audience_rules', function (Blueprint $table) {
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audience_rule_id')->constrained()->cascadeOnDelete();
            $table->primary(['announcement_id', 'audience_rule_id']);
        });

        $this->addCheckConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_audience_rules');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('audience_rules');
    }

    /**
     * Native check constraints on PostgreSQL.
     *
     * The values stay strings (rather than a PG enum type) so the documented
     * vocabulary can be extended without an ALTER TYPE migration. On SQLite the
     * constraint is skipped: the column CHECK is not supported there and enum
     * casts already enforce the values at the model layer.
     */
    private function addCheckConstraints(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE announcements ADD CONSTRAINT announcements_status_check CHECK (status IN (%s))',
            $this->quoteList(AnnouncementStatus::values())
        ));

        DB::statement(sprintf(
            'ALTER TABLE announcements ADD CONSTRAINT announcements_priority_check CHECK (priority IN (%s))',
            $this->quoteList(Priority::values())
        ));

        DB::statement(sprintf(
            'ALTER TABLE audience_rules ADD CONSTRAINT audience_rules_type_check CHECK (audience_type IN (%s))',
            $this->quoteList(AudienceType::values())
        ));
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

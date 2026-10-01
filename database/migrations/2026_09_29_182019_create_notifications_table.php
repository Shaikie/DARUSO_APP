<?php

use App\Enums\Priority;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database notifications, one row per intended recipient.
 *
 * Broadcast targeting is recorded on `notification_audience_rules` so the
 * intent is auditable even though each reader still receives their own row
 * (notifications must be individually readable and never deleted on read).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->string('priority', 10)->default(Priority::Normal->value);
            $table->timestamp('read_at')->nullable();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamps();

            $table->index('recipient_id');
            $table->index('read_at');
            $table->index('sender_id');
            $table->index(['recipient_id', 'read_at'], 'notifications_recipient_unread_index');
        });

        Schema::create('notification_audience_rules', function (Blueprint $table) {
            $table->foreignId('notification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audience_rule_id')->constrained()->cascadeOnDelete();
            $table->primary(['notification_id', 'audience_rule_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_audience_rules');
        Schema::dropIfExists('notifications');
    }
};

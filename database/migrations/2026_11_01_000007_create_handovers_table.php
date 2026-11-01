<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Handover records between outgoing and incoming officers.
 *
 * Records preserve institutional knowledge across terms and are never deleted.
 * Acknowledgement is recorded explicitly so the organisation can see which
 * handovers have been received.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handovers', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('summary');
            $table->text('outstanding_items')->nullable();
            $table->text('recommendations')->nullable();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ministry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('committee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('leadership_term_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index('from_user_id');
            $table->index('to_user_id');
            $table->index('status');
            $table->index('ministry_id');
            $table->index('committee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handovers');
    }
};

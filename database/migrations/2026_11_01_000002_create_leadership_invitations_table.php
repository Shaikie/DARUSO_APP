<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Single-use leadership invitations.
 *
 * Only the hash of the token is stored; the plaintext token exists solely in the
 * signed link emailed to the invitee. An invitation records the exact role and
 * organisational scope it will grant, so acceptance cannot escalate beyond what
 * the inviter was authorised to give.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leadership_invitations', function (Blueprint $table): void {
            $table->id();
            $table->string('email');
            $table->string('role_name', 40);
            $table->foreignId('ministry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('committee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('leadership_term_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['email', 'status']);
            $table->index('status');
            $table->index('expires_at');
            $table->index('role_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leadership_invitations');
    }
};

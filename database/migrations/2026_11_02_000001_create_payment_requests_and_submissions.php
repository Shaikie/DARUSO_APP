<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('TZS');
            $table->text('instructions')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->string('audience', 20)->default('all');
            $table->string('college')->nullable();
            $table->string('programme')->nullable();
            $table->unsignedTinyInteger('year_of_study')->nullable();
            $table->string('hostel')->nullable();
            $table->string('status', 20)->default('open');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_at']);
            $table->index(['audience', 'college', 'programme']);
        });

        Schema::create('payment_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_paid', 12, 2);
            $table->string('transaction_reference', 120);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->unique(['payment_request_id', 'user_id'], 'payment_submissions_request_user_unique');
            $table->index(['payment_request_id', 'status']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_submissions');
        Schema::dropIfExists('payment_requests');
    }
};

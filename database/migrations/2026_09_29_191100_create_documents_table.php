<?php

use App\Enums\DocumentVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stored documents.
 *
 * Files live on the private `local` disk and are only reachable through an
 * authorized download route, so visibility is enforced server-side rather than
 * by relying on unguessable URLs. `supersedes_id` models versioning so a new
 * upload can replace an older one without destroying history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 50)->default('other');
            $table->foreignId('uploader_id')->constrained('users')->cascadeOnDelete();
            $table->string('visibility', 20)->default(DocumentVisibility::Students->value);

            // Scoping for the ministry/committee/group visibilities.
            $table->foreignId('ministry_id')->nullable()->constrained('ministries')->nullOnDelete();
            $table->foreignId('committee_id')->nullable()->constrained('committees')->nullOnDelete();
            $table->unsignedBigInteger('group_id')->nullable();

            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_mime_type', 127)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('supersedes_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();

            $table->index('visibility');
            $table->index('category');
            $table->index('uploader_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

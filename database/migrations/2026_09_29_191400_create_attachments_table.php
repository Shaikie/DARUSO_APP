<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Polymorphic attachments shared by announcements, complaints, meetings, events
 * and documents.
 *
 * Files are stored on the private disk under a generated name; the original
 * filename is retained for display only and is never used to build a path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');
            $table->string('disk', 32)->default('local');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_mime_type', 127)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('collection', 40)->default('default');
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id', 'collection'], 'attachments_morph_collection_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};

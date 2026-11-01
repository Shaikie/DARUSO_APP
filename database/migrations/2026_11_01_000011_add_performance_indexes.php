<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance and correctness additions to existing tables.
 *
 * `batch_key` lets a broadcast group its inserted rows without a fragile
 * title/recipient re-query. The remaining indexes cover the filters used by the
 * dashboards, listings, calendar and global search.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->string('batch_key', 64)->nullable()->after('action_url');
            $table->index('batch_key');
        });

        Schema::table('complaints', function (Blueprint $table): void {
            $table->index(['status', 'created_at'], 'complaints_status_created_index');
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->index('created_at');
        });

        Schema::table('post_comments', function (Blueprint $table): void {
            $table->index(['post_id', 'is_hidden'], 'post_comments_visible_index');
        });
    }

    public function down(): void
    {
        Schema::table('post_comments', function (Blueprint $table): void {
            $table->dropIndex('post_comments_visible_index');
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex(['created_at']);
        });

        Schema::table('complaints', function (Blueprint $table): void {
            $table->dropIndex('complaints_status_created_index');
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropIndex(['batch_key']);
            $table->dropColumn('batch_key');
        });
    }
};

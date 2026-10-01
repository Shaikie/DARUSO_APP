<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('posts', function (Blueprint $table): void {
   $table->id(); $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
   $table->string('title'); $table->string('excerpt', 500)->nullable(); $table->longText('content');
   $table->string('cover_image_url')->nullable(); $table->boolean('is_published')->default(false);
   $table->timestamp('published_at')->nullable(); $table->unsignedInteger('share_count')->default(0); $table->timestamps();
   $table->index(['is_published','published_at']); $table->index('author_id');
  });
  Schema::create('post_likes', function (Blueprint $table): void {
   $table->foreignId('post_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
   $table->timestamps(); $table->primary(['post_id','user_id']);
  });
  Schema::create('post_comments', function (Blueprint $table): void {
   $table->id(); $table->foreignId('post_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
   $table->text('content'); $table->timestamps(); $table->index(['post_id','created_at']); $table->index('user_id');
  });
 }
 public function down(): void { Schema::dropIfExists('post_comments'); Schema::dropIfExists('post_likes'); Schema::dropIfExists('posts'); }
};
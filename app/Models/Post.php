<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Post extends Model {
 use HasFactory;
 protected $fillable=['author_id','title','excerpt','content','cover_image_url','is_published','published_at','share_count'];
 public function author(): BelongsTo { return $this->belongsTo(User::class,'author_id'); }
 public function likes(): BelongsToMany { return $this->belongsToMany(User::class,'post_likes')->withTimestamps(); }
 public function comments(): HasMany { return $this->hasMany(PostComment::class); }
 public function scopePublished(Builder $query): Builder { return $query->where('is_published',true)->whereNotNull('published_at')->where('published_at','<=',now()); }
 public function isPublished(): bool { return $this->is_published && $this->published_at?->isPast(); }
 public function likedBy(User $user): bool { return $this->relationLoaded('likes') ? $this->likes->contains($user->getKey()) : $this->likes()->whereKey($user->getKey())->exists(); }
 protected function casts(): array { return ['is_published'=>'boolean','published_at'=>'datetime','share_count'=>'integer']; }
}
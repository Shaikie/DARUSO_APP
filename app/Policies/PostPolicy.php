<?php
namespace App\Policies;
use App\Enums\PermissionName;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
class PostPolicy {
 public function viewAny(User $user): bool { return $user->isStudent() || $user->isLeader(); }
 public function view(User $user, Post $post): bool { return $post->isPublished() || $post->author_id===$user->getKey(); }
 public function create(User $user): bool { return $user->hasPermission(PermissionName::PostCreate->value); }
 public function update(User $user, Post $post): bool { return $user->hasPermission(PermissionName::PostEdit->value) && ($post->author_id === $user->getKey() || $user->hasPermission(PermissionName::PostModerate->value)); }
 public function delete(User $user, Post $post): bool { return $user->hasPermission(PermissionName::PostDelete->value) && ($post->author_id===$user->getKey() || $user->hasPermission(PermissionName::PostModerate->value)); }
 public function publish(User $user, Post $post): bool { return $user->hasPermission(PermissionName::PostPublish->value); }
 public function comment(User $user, Post $post): bool { return $user->hasPermission(PermissionName::PostComment->value) && $post->isPublished(); }
 public function like(User $user, Post $post): bool { return $post->isPublished(); }
 public function share(User $user, Post $post): bool { return $post->isPublished(); }
 public function deleteComment(User $user, Post $post, PostComment $comment): bool { return $comment->post_id===$post->getKey() && ($comment->user_id===$user->getKey() || $user->hasPermission(PermissionName::PostModerate->value)); }
}
<?php
namespace Tests\Feature;
use App\Enums\PermissionName;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class PostTest extends TestCase {
 use RefreshDatabase;
 private function makeLeader(): User {
  $user=User::factory()->create(); $role=Role::firstOrCreate(['name'=>'admin']);
  $ids=collect(PermissionName::cases())->map(fn(PermissionName $p)=>Permission::firstOrCreate(['name'=>$p->value])->id);
  $role->permissions()->sync($ids); $user->roles()->attach($role); return $user;
 }
 private function makeStudent(): User {
  $user=User::factory()->create(); $role=Role::firstOrCreate(['name'=>'student']);
  $permission=Permission::firstOrCreate(['name'=>PermissionName::PostComment->value]);
  $role->permissions()->syncWithoutDetaching([$permission->id]); $user->roles()->attach($role);
  StudentProfile::factory()->create(['user_id'=>$user->id]); return $user;
 }
 public function test_leader_can_create_and_publish_a_post(): void {
  $leader=$this->makeLeader();
  $this->actingAs($leader)->post(route('leader.posts.store'),['title'=>'Daily DARUSO update','content'=>'This is a useful daily update for students.','is_published'=>1])->assertRedirect(route('leader.posts.index'));
  $post=Post::firstOrFail(); $this->assertTrue($post->isPublished());
  $this->actingAs($leader)->get(route('student.posts.show',$post))->assertOk();
 }
 public function test_student_can_like_comment_and_share_published_post(): void {
  $leader=$this->makeLeader(); $student=$this->makeStudent();
  $post=Post::create(['author_id'=>$leader->id,'title'=>'Community story','content'=>'A published community story.','is_published'=>true,'published_at'=>now()]);
  $this->actingAs($student)->post(route('student.posts.like',$post))->assertRedirect();
  $this->assertDatabaseHas('post_likes',['post_id'=>$post->id,'user_id'=>$student->id]);
  $this->actingAs($student)->post(route('student.posts.comments.store',$post),['content'=>'Great update.'])->assertRedirect();
  $this->assertDatabaseHas('post_comments',['post_id'=>$post->id,'user_id'=>$student->id]);
  $this->actingAs($student)->post(route('student.posts.share',$post))->assertRedirect();
  $this->assertDatabaseHas('posts',['id'=>$post->id,'share_count'=>1]);
 }
 public function test_unpublished_post_is_not_visible_to_students(): void {
  $leader=$this->makeLeader(); $student=$this->makeStudent();
  $post=Post::create(['author_id'=>$leader->id,'title'=>'Private draft','content'=>'Draft content.','is_published'=>false]);
  $this->actingAs($student)->get(route('student.posts.show',$post))->assertForbidden();
 }
}
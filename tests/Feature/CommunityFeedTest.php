<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostReaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_users_land_on_the_community_feed(): void
    {
        $this->seed();

        $student = User::where('email', 'student@daruso.local')->firstOrFail();

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertRedirect(route('student.posts.index'));
    }

    public function test_administrators_land_on_the_administration_dashboard(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@daruso.local')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertRedirect(route('leader.dashboard'));
    }

    public function test_a_user_can_add_and_toggle_a_post_reaction(): void
    {
        $this->seed();

        $student = User::where('email', 'student@daruso.local')->firstOrFail();
        $post = Post::query()->published()->firstOrFail();

        $this->actingAs($student)
            ->post(route('student.posts.react', $post), ['type' => 'love'])
            ->assertRedirect();

        $this->assertDatabaseHas('post_reactions', [
            'post_id' => $post->getKey(),
            'user_id' => $student->getKey(),
            'type' => 'love',
        ]);

        $this->actingAs($student)
            ->post(route('student.posts.react', $post), ['type' => 'love'])
            ->assertRedirect();

        $this->assertDatabaseMissing('post_reactions', [
            'post_id' => $post->getKey(),
            'user_id' => $student->getKey(),
        ]);
    }

    public function test_a_user_can_switch_between_reaction_types(): void
    {
        $this->seed();

        $student = User::where('email', 'student@daruso.local')->firstOrFail();
        $post = Post::query()->published()->firstOrFail();

        $this->actingAs($student)
            ->post(route('student.posts.react', $post), ['type' => 'support'])
            ->assertRedirect();

        $this->actingAs($student)
            ->post(route('student.posts.react', $post), ['type' => 'insightful'])
            ->assertRedirect();

        $this->assertDatabaseHas('post_reactions', [
            'post_id' => $post->getKey(),
            'user_id' => $student->getKey(),
            'type' => 'insightful',
        ]);

        $this->assertDatabaseMissing('post_reactions', [
            'post_id' => $post->getKey(),
            'user_id' => $student->getKey(),
            'type' => 'support',
        ]);
    }

    public function test_a_user_has_at_most_one_reaction_per_post(): void
    {
        $this->seed();
        $student = User::where('email', 'student@daruso.local')->firstOrFail();
        $post = Post::query()->published()->firstOrFail();

        $this->actingAs($student)->post(route('student.posts.react', $post), ['type' => 'love']);
        $this->actingAs($student)->post(route('student.posts.react', $post), ['type' => 'insightful']);

        $this->assertSame(1, PostReaction::where('post_id', $post->getKey())
            ->where('user_id', $student->getKey())
            ->count());
    }
}

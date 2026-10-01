<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AllRoutesRenderTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => 'student'])->id);
        StudentProfile::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    private function makeLeader(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => 'admin'])->id);

        return $user;
    }

    /**
     * Walk every GET route under the given prefix and assert it renders.
     */
    private function assertPrefixRenders(string $prefix, User $user, int $placeholderId = 1): void
    {
        $failures = [];
        $checked = 0;

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            if (! in_array($route->methods()[0] ?? null, ['GET', 'HEAD'], true)) {
                continue;
            }

            if (! str_starts_with($uri, $prefix)) {
                continue;
            }

            // Only exercise this app's own routes, not framework/boost routes.
            if (! str_starts_with($route->getActionName(), 'App\\Http\\Controllers')) {
                continue;
            }

            $target = preg_replace('/{(\w+)\??}/', (string) $placeholderId, $uri);

            $response = $this->actingAs($user)->get('/'.$target);
            $checked++;

            if ($response->status() >= 500) {
                $failures[] = $uri.' => '.$response->status();
            }
        }

        $this->assertSame([], $failures, "Failing routes in {$prefix}:\n".implode("\n", $failures));
        $this->assertGreaterThan(5, $checked, "No routes were exercised for {$prefix}.");
    }

    public function test_public_pages_render(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/forgot-password')->assertOk();
    }

    public function test_every_student_route_renders(): void
    {
        $this->assertPrefixRenders('student', $this->makeStudent());
    }

    public function test_every_leader_route_renders(): void
    {
        $this->assertPrefixRenders('leader', $this->makeLeader());
    }
}

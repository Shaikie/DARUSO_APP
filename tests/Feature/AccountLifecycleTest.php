<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\User;
use App\Services\StudentVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AccountLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspending_an_account_does_not_assume_database_sessions_are_enabled(): void
    {
        config(['session.driver' => 'file']);
        Schema::dropIfExists('sessions');

        $actor = User::factory()->create();
        $target = User::factory()->create();

        $updated = app(StudentVerificationService::class)->setAccountStatus(
            $target,
            AccountStatus::Suspended,
            $actor,
        );

        $this->assertSame(AccountStatus::Suspended, $updated->account_status);
    }
}

<?php

namespace Tests\Feature;

use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivatePaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_request_and_confirmation_are_private_to_the_student_and_authorised_leaders(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@daruso.local')->firstOrFail();
        $student = User::query()->where('email', 'student@daruso.local')->firstOrFail();
        $otherStudent = User::query()->where('email', 'alice@daruso.local')->firstOrFail();

        $created = $this->actingAs($admin)->post(route('leader.payments.store'), [
            'title' => 'Annual student contribution',
            'description' => 'Annual contribution for student activities.',
            'amount' => '15000',
            'instructions' => 'Pay using the approved DARUSO channel and keep your transaction reference.',
            'audience' => 'all',
        ]);

        $paymentRequest = PaymentRequest::query()->where('title', 'Annual student contribution')->firstOrFail();
        $created->assertRedirect(route('leader.payments.show', $paymentRequest));

        $this->actingAs($student)
            ->post(route('student.payments.submit', $paymentRequest), [
                'amount_paid' => '15000',
                'transaction_reference' => 'TXN-PRIVATE-001',
                'notes' => 'Paid through the stated channel.',
            ])
            ->assertRedirect(route('student.payments.show', $paymentRequest));

        $this->assertDatabaseHas('payment_submissions', [
            'payment_request_id' => $paymentRequest->getKey(),
            'user_id' => $student->getKey(),
            'transaction_reference' => 'TXN-PRIVATE-001',
            'status' => 'pending',
        ]);

        $this->actingAs($otherStudent)
            ->get(route('student.payments.show', $paymentRequest))
            ->assertOk()
            ->assertDontSee('TXN-PRIVATE-001')
            ->assertDontSee($student->name);

        $this->actingAs($admin)
            ->get(route('leader.payments.show', $paymentRequest))
            ->assertOk()
            ->assertSee('TXN-PRIVATE-001')
            ->assertSee($student->name);
    }

    public function test_only_authorised_payment_managers_can_access_leader_payment_management(): void
    {
        $this->seed();
        $ministryLeader = User::query()->where('email', 'ministry@daruso.local')->firstOrFail();

        $this->actingAs($ministryLeader)
            ->get(route('leader.payments.index'))
            ->assertForbidden();
    }
}

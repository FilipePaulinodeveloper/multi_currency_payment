<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\PaymentRequestStatus;
use App\Enums\Role;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentRequestUpdateStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_admin_can_approve_payment_request()
    {
        // Arrange
        $currency = Currency::BRL;
        $admin = User::factory()->create([
            'role' => Role::FINANCE_ADMIN,
            'currency' => $currency
        ]);
        

        $paymentRequest = PaymentRequest::factory()->create([
            'status' => PaymentRequestStatus::PENDING,
        ]);

        $this->actingAs($admin, 'api');

        // Act

        $response = $this->putJson(
            "/api/payment-requests/{$paymentRequest->id}/status",
            [
                'status' => PaymentRequestStatus::APPROVED->value,
            ]
        );

        // Assert

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'message' => 'Payment request status updated successfully',
        ]);

        $this->assertDatabaseHas('payment_requests', [
            'id' => $paymentRequest->id,
            'status' => PaymentRequestStatus::APPROVED->value,
        ]);
    }

    public function test_non_finance_admin_cannot_update_payment_request()
    {
        $currency = Currency::BRL;
        $user = User::factory()->create([
            'role' => Role::EMPLOYEE,
            'currency' => $currency
        ]);

        $paymentRequest = PaymentRequest::factory()->create([
            'status' => PaymentRequestStatus::PENDING,
        ]);

        $this->actingAs($user, 'api');

        $response = $this->putJson(
            "/api/payment-requests/{$paymentRequest->id}/status",
            [
                'status' => PaymentRequestStatus::APPROVED->value,
            ]
        );

        $response->assertStatus(403);
    }
}
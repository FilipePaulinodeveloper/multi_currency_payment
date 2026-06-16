<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PaymentRequest;
use App\Enums\PaymentRequestStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use App\Contracts\CurrencyAdapter;
use App\Enums\Currency;
use App\Enums\Role;
use App\Integrations\ExchangeRate\ExchangeRateAdapter;

class PaymentRequestStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_payment_request()
    {
        $role = Role::EMPLOYEE;
        $currency = Currency::BRL;
        $user = User::factory()->create(['role' => $role , 'currency' => $currency]);

        $this->actingAs($user,  'api');

        $adapter = Mockery::mock(ExchangeRateAdapter::class);
        

        $adapter->shouldReceive('convert')
            ->once()
            ->andReturn([
                'amount' => 150.00,
                'exchange_rate' => 1.5,
            ]);

        $this->app->instance(ExchangeRateAdapter::class, $adapter);

      
        $payload = [
            'description' => 'Pagamento de serviço',
            'amount_local' => 100,
            'currency' => 'BRL'
        ];

        
        $response = $this->postJson('/api/payment-requests', $payload);
        
        
        $response->assertStatus(201);

        
        $this->assertDatabaseHas('payment_requests', [
            'user_id' => $user->id,
            'description' => 'Pagamento de serviço',
            'amount_local' => 100,
            'amount_eur' => 150,
            'exchange_rate' => 1.5,
            'status' => PaymentRequestStatus::PENDING->value,
        ]);
    }
}
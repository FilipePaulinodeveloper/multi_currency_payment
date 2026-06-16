<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\PaymentRequestStatus;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentRequestFactory extends Factory
{
    public function definition(): array
    {
        $currency = Currency::BRL;
        return [
            'user_id' => User::factory([
                'role' => Role::FINANCE_ADMIN,
                'currency' => $currency
            ]),

            'description' => fake()->sentence(),

            'amount_local' => fake()->randomFloat(2, 10, 5000),

            'currency' => fake()->randomElement([
                Currency::BRL,
                Currency::USD,
                Currency::EUR,
                Currency::GBP,
                Currency::JPY,
            ]),

            'amount_eur' => fake()->randomFloat(2, 10, 5000),

            'exchange_rate' => fake()->randomFloat(4, 0.1, 2),

            'exchange_source' => 'ExchangeRateAPI',

            'exchange_timestamp' => now(),

            'status' => PaymentRequestStatus::PENDING,
        ];
    }
}
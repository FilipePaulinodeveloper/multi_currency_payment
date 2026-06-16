<?php

namespace Database\Seeders;

use App\Enums\Currency;
use App\Enums\PaymentRequestStatus;
use App\Enums\Role;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Busca usuários employee
        $employees = User::where('role', Role::EMPLOYEE)->take(1)->get();

        $currencies = [
            Currency::BRL,
            Currency::USD,
            Currency::EUR,
            Currency::GBP,
            Currency::JPY,
        ];

        foreach ($employees as $index => $employee) {

            PaymentRequest::create([
                'user_id' => $employee->id,

                'description' => 'Payment request ' . ($index + 1),

                'amount_local' => rand(100, 5000),

                'currency' => $currencies[$index]->value,

                'status' => PaymentRequestStatus::PENDING,

                // valores fictícios
                'amount_eur' => 0,

                'exchange_rate' => 1,

                'exchange_source' => 'Seeder',

                'exchange_timestamp' => now(),
            ]);
        }
    }
}
<?php

namespace Database\Seeders;

use App\Enums\Currency;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'name' => 'João Silva',                
                'password' => '12345678',
                'currency' => Currency::BRL,
            ],
            [
                'name' => 'Maria Souza',                
                'currency' => Currency::USD,
            ],
            [
                'name' => 'Pedro Santos',                
                'currency' => Currency::EUR,
            ],
            [
                'name' => 'Ana Oliveira',                
                'currency' => Currency::GBP,
            ],
            [
                'name' => 'Carlos Lima',                
                'currency' => Currency::JPY,
            ],
        ];

        foreach ($employees as $employee) {

            User::factory()->create([
                'name' => $employee['name'],
                'password' =>  Hash::make('12345678'),
                'role' => Role::EMPLOYEE,
                'currency' => $employee['currency'],
                
            ]);
        }
    }
}
<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FinanceAdmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
                'name' => 'Filipe',
                'email' => 'finance@admin.com',
                'password' =>  Hash::make('12345678'),
                'role' => Role::FINANCE_ADMIN,
                'currency' => 'EUR',
                
            ]);
    }
}

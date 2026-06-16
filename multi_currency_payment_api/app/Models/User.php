<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\Currency;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'currency',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'currency' => Currency::class,
        ];
    }

        /**
        * Get the payment requests created by this user
        */

    public function paymentRequests()
    {
        return $this->hasMany(PaymentRequest::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::FINANCE_ADMIN;
    }

    public function isEmployee(): bool
    {
        return $this->role === Role::EMPLOYEE;
    } 
}

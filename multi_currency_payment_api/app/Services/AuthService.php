<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login(array $credentials): array
    {
        
        if (!Auth::attempt($credentials)) {
        
            throw new Exception('Invalid credentials');
        }
        

        $user = Auth::user();
        
        $token = $user->createToken('api-token')->accessToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }
}
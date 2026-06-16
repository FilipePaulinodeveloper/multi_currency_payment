<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login(array $credentials): array
    {
        
       if (!Auth::attempt($credentials)) {
            abort(401, 'Email or password is incorrect. Please verify and try again.');
       }
        

        $user = Auth::user();
        
        $token = $user->createToken('api-token')->accessToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }
}
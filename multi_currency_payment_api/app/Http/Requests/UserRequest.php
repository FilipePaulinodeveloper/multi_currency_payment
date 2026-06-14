<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Enums\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {        
        $userId = $this->route('user');        
        
        if ($userId instanceof \App\Models\User) {

            $userId = $userId->id;
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'password' => $userId ? ['nullable', 'string', 'min:8'] : ['required', 'string', 'min:8'],
            'role' => [
                'required',
                Rule::enum(Role::class),
            ],
            'currency' => [
                'required',
                Rule::enum(Currency::class),
            ]           
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            //
        ];
    }
}

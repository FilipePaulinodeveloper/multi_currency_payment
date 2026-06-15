<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequestRequest extends FormRequest
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
        return [
            'description' => [
                'required',
                'string',
                'min:5',
                'max:1000',
            ],
            'amount_local' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999999.99',
            ],
            'currency' => [
                'required',
                'string',
                'size:3',
                Rule::enum(Currency::class),
            ],
        ];
    }

    /**
     * Get the validation messages.
     */
    public function messages(): array
    {
        return [
            'description.required' => 'Description is required',
            'description.min' => 'Description must be at least 5 characters',
            'description.max' => 'Description must not exceed 1000 characters',
            'amount_local.required' => 'Amount is required',
            'amount_local.numeric' => 'Amount must be a valid number',
            'amount_local.min' => 'Amount must be greater than 0',
            'amount_local.max' => 'Amount cannot exceed 999,999,999.99',
            'currency.required' => 'Currency is required',
            'currency.size' => 'Currency code must be exactly 3 characters',
            'currency.enum' => 'Selected currency is not supported',
        ];
    }

    /**
     * Get the validated data.
     */
    public function validated($key = null, $default = null)
    {
        return parent::validated($key, $default);
    }
}

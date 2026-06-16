<?php

namespace App\Integrations\ExchangeRate;

use App\Enums\Currency;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class ExchangeRateAdapter
{
    private string $baseUrl;
    private string $apiKey;
    
    public function __construct()
    {        
        $this->apiKey = env('EXCHANGE_RATE_API_KEY');
        $this->baseUrl = 'https://v6.exchangerate-api.com/v6/' . $this->apiKey . '/latest';
        
        if (!$this->apiKey) {
            throw new Exception('Exchange Rate API key not configured', 500);
        }
    }

    public function getRates(string $baseCurrency): array
    {
        //Http::withoutVerifying() ONLY FOR TESTING PURPOSES, MAKE SURE TO REMOVE IT IN PRODUCTION AND USE A VALID SSL CERTIFICATE
        $response = Http::withoutVerifying()->get("{$this->baseUrl}/{$baseCurrency}");
        
        if ($response->failed()) {
            
            abort($response->status(), "Error to get exchange rates: " . $response->body());
            // throw new \Exception("Error to get exchange rates: " . $response->body());      

        }   

        return $response->json();
    }

    private function validateCurrency(string $currency)
    {
        if (!Currency::tryFrom($currency)) {
        // //   throw new \Exception("Currency '{$currency}' is not supported", 422);
            throw ValidationException::withMessages([
                'currency' => "Currency '{$currency}' is not supported"
            ]);
        }
    }

    public function convert(string $from, float $amount, string $to = 'EUR'): Array
    {
      
        
        $this->validateCurrency($from);
        $this->validateCurrency($to);
        
        $date = $this->getRates($from);
        
        if ($amount <= 0) {
            abort(422, "Amount must be greater than zero");
        }

         if ($from === $to) {
            return ['amount' => round($amount, 2), 'exchange_rate' => 1.00];
        }
        
        $rate = $date['conversion_rates'][$to] ;  


        return [
            'amount' => round($amount * $rate, 2),
            'exchange_rate' => $rate
        ];
    }
}
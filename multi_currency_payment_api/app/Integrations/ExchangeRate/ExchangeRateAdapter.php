<?php

namespace App\Integrations\ExchangeRate;

use App\Enums\Currency;
use Exception;
use Illuminate\Support\Facades\Http;

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
            abort(422, "Currency '{$currency}' is not supported");
        }
    }

    public function convert(string $from, float $amount, string $to = 'EUR'): float
    {
        if ($amount <= 0) {
          return 0.00;
        }
        else if ($from === $to) {
            return round($amount, 2);
        }

        $this->validateCurrency($from);
        $this->validateCurrency($to);
        $data = $this->getRates($from);

        $rate = $data['conversion_rates'][$to] ;  


        return round($amount * $rate, 2);
    }
}
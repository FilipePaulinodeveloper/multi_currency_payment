<?php

namespace App\Integrations\ExchangeRate;

use Exception;
use Illuminate\Support\Facades\Http;

class ExchangeRateAdapter
{
    private string $baseUrl;
    private string $apiKey;
    
    public function __construct()
    {        
        $this->apiKey = config('services.exchange_rate.api_key');
        $this->baseUrl = config('services.exchange_rate.base_url', 'https://v6.exchangerate-api.com/v6');
        
        if (!$this->apiKey) {
            throw new Exception('Exchange Rate API key not configured', 500);
        }
    }

    public function getRates(string $baseCurrency): array
    {
        $response = Http::get("{$this->baseUrl}/{$baseCurrency}");
        
        if ($response->failed()) {
            throw new \Exception("Error to get exchange rates: " . $response->body());
        }   

        return $response->json();
    }

    public function convert(string $from, string $to, float $amount): float
    {
        $data = $this->getRates($from);

        $rate = $data['conversion_rates'][$to] ?? null;

        if (!$rate) {
            throw new \Exception("Currency {$to} not found");
        }

        return $amount * $rate;
    }
}
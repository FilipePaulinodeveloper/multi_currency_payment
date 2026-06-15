<?php
namespace App\Services;

use App\Integrations\ExchangeRate\ExchangeRateAdapter;

class CurrencyService
{
    public function __construct(
        private ExchangeRateAdapter $adapter
    ) {}

    public function convert(string $from, float $amount, string $to = 'EUR'): array
    {       
       

        $converted = $this->adapter->convert($from, $amount, $to);
        
        return [
            'from' => $from,
            'to' => $to,
            'exchange_rate' => $converted['exchange_rate'],
            'amount' => $amount,
            'converted' => $converted['amount'],
        ];
    }
}
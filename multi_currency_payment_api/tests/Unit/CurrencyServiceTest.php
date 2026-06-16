<?php

namespace Tests\Unit;

use App\Services\CurrencyService;
use App\Contracts\CurrencyAdapter;
use App\Integrations\ExchangeRate\ExchangeRateAdapter;
use Mockery;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    public function test_convert_currency()
    {
        $adapter = Mockery::mock(ExchangeRateAdapter::class);

        $adapter
            ->shouldReceive('convert')
            ->once()
            ->with('BRL', 100, 'USD')
            ->andReturn([
                'amount' => 18.20,
                'exchange_rate' => 0.182
            ]);

        $service = new CurrencyService($adapter);

        $result = $service->convert('BRL', 100, 'USD');

        $this->assertEquals([
            'from' => 'BRL',
            'to' => 'USD',
            'exchange_rate' => 0.182,
            'amount' => 100,
            'converted' => 18.20,
        ], $result);
    }
}
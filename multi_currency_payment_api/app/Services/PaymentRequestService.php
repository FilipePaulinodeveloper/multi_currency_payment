<?php

namespace App\Services;

use App\Enums\PaymentRequestStatus;
use App\Integrations\ExchangeRate\ExchangeRateAdapter;
use App\Models\PaymentRequest;
use Illuminate\Support\Facades\Cache;
use Override;

class PaymentRequestService extends BaseSimpleCRUDService
{
    public function __construct(PaymentRequest $model)
    {
        
        parent::__construct($model);
    }

    public function store(array $data): PaymentRequest
    {        
        $data['user_id'] = auth()->id();
        
        $conversion = $this->calculateCurrencyConversion($data);
        $data['amount_eur'] = $conversion['converted'];
        $data['exchange_rate'] = $conversion['exchange_rate'];
        $data['exchange_source'] = 'ExchangeRateAPI';
        $data['exchange_timestamp'] = now();
        $data['status'] = PaymentRequestStatus::PENDING;

        $paymentRequest = $this->model->create($data);          
        Cache::forget($this->cacheKey);
        return $paymentRequest;
    }

        public function update(string $id, array $data)
    {
        $paymentRequest = $this->model->findOrFail($id);        

        $conversion = $this->calculateCurrencyConversion($data);
        $data['amount_eur'] = $conversion['converted'];
        $data['exchange_rate'] = $conversion['exchange_rate'];
        $data['exchange_source'] = 'ExchangeRateAPI';
        $data['exchange_timestamp'] = now();        

        $paymentRequest->update($data);
        Cache::forget($this->cacheKey);
        return $paymentRequest;
    }

    private function calculateCurrencyConversion(array $data): array
    {
        $currencyService = new CurrencyService(new ExchangeRateAdapter());
        return $currencyService->convert($data['currency'], $data['amount_local']);
    }
}
<?php

namespace App\Services;

use App\Enums\PaymentRequestStatus;
use App\Enums\Role;
use App\Integrations\ExchangeRate\ExchangeRateAdapter;
use App\Models\PaymentRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Override;

class PaymentRequestService extends BaseSimpleCRUDService
{
    // protected  CurrencyService $currencyService;
    private CurrencyService $currencyService;

    public function __construct(
        PaymentRequest $model,
        CurrencyService $currencyService
    ) {
        $this->currencyService = $currencyService;

        parent::__construct($model);
    }

    public function index(?array $filters = null)
    {
        
        $query = $this->model->query();

        if (!empty($filters['status'])) {
            $status = PaymentRequestStatus::tryFrom($filters['status']);          
            if (!$status) {
                throw ValidationException::withMessages([
                    'status' => 'Invalid status. Valid values: ' .
                        implode(', ', PaymentRequestStatus::values())
                ]);
            }
        }
        
        

        $query = $this->model->query();
        $query = $this->model->VisibleTo($query);
        
        if (isset($status)) {
            $query->status($status);
        }

        return $query->paginate(15);
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
        
        return $paymentRequest;
    }

    public function updateStatus(string $id, array $data)
    {
        if (auth()->user()->role !== Role::FINANCE_ADMIN) {
            abort(403, 'Only finance admins can update payment requests');
        }

        $paymentRequest = $this->model->findOrFail($id);                      

        $paymentRequest->update($data);
        
        return $paymentRequest;
    }

    private function calculateCurrencyConversion(array $data): array
    {
        // $currencyService = new CurrencyService(new ExchangeRateAdapter());
        
        return $this->currencyService->convert($data['currency'], $data['amount_local']);
    }

    
}
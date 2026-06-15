<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequestRequest;
use App\Models\PaymentRequest;
use App\Services\PaymentRequestService;
use Exception;
use Illuminate\Http\JsonResponse;

class PaymentRequestController extends BaseSimpleCRUDController
{    

    public function __construct(PaymentRequestService $service)
    {
        parent::__construct($service);
    } 
   
}

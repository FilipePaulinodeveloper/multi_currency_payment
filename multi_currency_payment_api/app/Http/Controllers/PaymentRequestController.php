<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequestRequest;
use App\Http\Requests\PaymentRequestStatusUpdate;
use App\Models\PaymentRequest;
use App\Services\PaymentRequestService;
use Exception;
use Illuminate\Http\JsonResponse;
use Override;

class PaymentRequestController extends BaseSimpleCRUDController
{    

    public function __construct(PaymentRequestService $service)
    {
        parent::__construct($service);
    } 

    
    public function updateStatus(string $id , PaymentRequestStatusUpdate $request)
    {
        $request->validated();
        $requestData = $request->only('status');
        $paymentRequest = $this->service->updateStatus($id, $requestData);

        return response()->json([
            'message' => 'Payment request status updated successfully',
            'data' => $paymentRequest
        ], 200);
      
    }
   
}

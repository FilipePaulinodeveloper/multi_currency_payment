<?php

namespace App\Console\Commands;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpiredPendingPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expired-pending-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'expires pending payments that are older than 48 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        $limite = Carbon::now()->subHours(48); 
        
        
        $paymentsAffected = PaymentRequest::pending()
            ->olderThan($limite)          
            ->update(['status' => PaymentRequestStatus::EXPIRED]);
        
        $this->info("Success: {$paymentsAffected} payments pending were expired.");
    }
}

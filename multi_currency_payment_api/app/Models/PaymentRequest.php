<?php

namespace App\Models;

use App\Enums\PaymentRequestStatus;
use App\Enums\Role;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentRequest extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentRequestFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'description',
        'amount_local',
        'currency',
        'amount_eur',
        'exchange_rate',
        'exchange_source',
        'exchange_timestamp',
        'status',
    ];

    protected $casts = [
        'amount_local' => 'decimal:2',
        'amount_eur' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'status' => PaymentRequestStatus::class,
        'exchange_timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this payment request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get pending requests
     */
    public function scopeStatus($query, PaymentRequestStatus $status)
    {
        return $query->where('status', $status instanceof PaymentRequestStatus
            ? $status->value
            : $status
        );
    }
 

    /**
     * Scope to get approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', PaymentRequestStatus::APPROVED);
    }

    // /**
    //  * Approve the payment request
    //  */
    // public function approve(): bool
    // {      

    //     return $this->update(['status' => PaymentRequestStatus::APPROVED]);
    // }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PaymentRequestStatus::PENDING);
    }
    
    public function scopeOlderThan(Builder $query, $limite): Builder
    {
        return $query->where('created_at', '<=', $limite);
    }

    public function scopeVisibleTo($query)
    {
        $user = auth()->user();
        
        if (!($user->role == Role::FINANCE_ADMIN)) {
            $query->where('user_id', $user->id);
        }
    }

  
   
}

<?php

namespace App\Models;

use App\Enums\PaymentRequestStatus;
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
    public function scopePending($query)
    {
        return $query->where('status', PaymentRequestStatus::PENDING);
    }

    /**
     * Scope to get approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', PaymentRequestStatus::APPROVED);
    }

    /**
     * Approve the payment request
     */
    public function approve(): bool
    {      

        return $this->update(['status' => PaymentRequestStatus::APPROVED]);
    }

    /**
     * Reject the payment request
     */
    public function reject(): bool
    {     
        return $this->update(['status' => PaymentRequestStatus::REJECTED]);
    }

    /**
     * Mark as expired
     */
    public function markExpired(): bool
    {        
        return $this->update(['status' => PaymentRequestStatus::EXPIRED]);
    }
}

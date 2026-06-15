<?php

namespace App\Enums;

enum PaymentRequestStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';    
    case EXPIRED = 'expired';

    /**
     * Get all values as array
     */
    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }

    /**
     * Get readable label
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',      
            self::EXPIRED => 'Expired',      
        };
    }

 

  

}

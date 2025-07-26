<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Authorized = 'authorized';
    case InProcess = 'in_process';
    case InMediation = 'in_mediation';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case ChargedBack = 'charged_back';

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isApproved(): bool
    {
        return $this === self::Approved;
    }

    public function isRejected(): bool
    {
        return $this === self::Rejected;
    }

    public function isCancelled(): bool
    {
        return $this === self::Cancelled;
    }

    public function isRefunded(): bool
    {
        return $this === self::Refunded;
    }
}

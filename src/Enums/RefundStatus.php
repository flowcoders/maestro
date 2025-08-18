<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum RefundStatus: string
{
    case APPROVED = 'APPROVED';
    case CANCELED = 'CANCELED';
    case IN_PROCESS = 'IN_PROCESS';
    case PENDING = 'PENDING';
    case REJECTED = 'REJECTED';

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }

    public function isInProcess(): bool
    {
        return $this === self::IN_PROCESS;
    }

    public function isCanceled(): bool
    {
        return $this === self::CANCELED;
    }
}

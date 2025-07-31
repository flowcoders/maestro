<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum PaymentStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case AUTHORIZED = 'AUTHORIZED';
    case IN_PROCESS = 'IN_PROCESS';
    case IN_MEDIATION = 'IN_MEDIATION';
    case REJECTED = 'REJECTED';
    case CANCELED = 'CANCELED';
    case REFUNDED = 'REFUNDED';
    case CHARGED_BACK = 'CHARGED_BACK';

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    public function isAuthorized(): bool
    {
        return $this === self::AUTHORIZED;
    }

    public function isInProcess(): bool
    {
        return $this === self::IN_PROCESS;
    }

    public function isInMediation(): bool
    {
        return $this === self::IN_MEDIATION;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }

    public function isCanceled(): bool
    {
        return $this === self::CANCELED;
    }

    public function isRefunded(): bool
    {
        return $this === self::REFUNDED;
    }

    public function isChargedBack(): bool
    {
        return $this === self::CHARGED_BACK;
    }
}

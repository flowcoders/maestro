<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case AUTHORIZED = 'authorized';
    case IN_PROCESS = 'in_process';
    case IN_MEDIATION = 'in_mediation';
    case REJECTED = 'rejected';
    case CANCELED = 'canceled';
    case REFUNDED = 'refunded';
    case CHARGED_BACK = 'charged_back';

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

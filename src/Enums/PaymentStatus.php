<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum PaymentStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case AUTHORIZED = 'AUTHORIZED';
    case IN_PROCESS = 'IN_PROCESS';
    case IN_DISPUTE = 'IN_DISPUTE';
    case REFUSED = 'REFUSED';
    case CANCELED = 'CANCELED';
    case REFUNDED = 'REFUNDED';
    case CHARGED_BACK = 'CHARGED_BACK';
    case OVERDUE = 'OVERDUE';
    case REFUND_REQUESTED = 'REFUND_REQUESTED';
    case REFUND_IN_PROGRESS = 'REFUND_IN_PROGRESS';
    case IN_ANALYSIS = 'IN_ANALYSIS';

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

    public function isInDispute(): bool
    {
        return $this === self::IN_DISPUTE;
    }

    public function isRefused(): bool
    {
        return $this === self::REFUSED;
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

    public function isOverdue(): bool
    {
        return $this === self::OVERDUE;
    }

    public function isRefundRequested(): bool
    {
        return $this === self::REFUND_REQUESTED;
    }

    public function isRefundInProgress(): bool
    {
        return $this === self::REFUND_IN_PROGRESS;
    }

    public function isInAnalysis(): bool
    {
        return $this === self::IN_ANALYSIS;
    }
}

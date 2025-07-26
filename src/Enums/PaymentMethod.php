<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum PaymentMethod: string
{
    case BANK_SLIP = 'bank_slip';
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case PIX = 'pix';

    public function isCreditCard(): bool
    {
        return $this === self::CREDIT_CARD;
    }

    public function isDebitCard(): bool
    {
        return $this === self::DEBIT_CARD;
    }

    public function isPix(): bool
    {
        return $this === self::PIX;
    }

    public function isBankSlip(): bool
    {
        return $this === self::BANK_SLIP;
    }
}

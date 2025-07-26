<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum PaymentMethod: string
{
    case CREDIT_CARD = 'credit_card';
    case PIX = 'pix';

    public function isCreditCard(): bool
    {
        return $this === self::CREDIT_CARD;
    }

    public function isPix(): bool
    {
        return $this === self::PIX;
    }
}

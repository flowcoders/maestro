<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum PaymentMethod: string
{
    case CREDIT_CARD = 'CREDIT_CARD';
    case PIX = 'PIX';

    public function isCreditCard(): bool
    {
        return $this === self::CREDIT_CARD;
    }

    public function isPix(): bool
    {
        return $this === self::PIX;
    }
}

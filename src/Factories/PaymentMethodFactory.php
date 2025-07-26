<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Factories;

use Flowcoders\Maestro\ValueObjects\PaymentMethod\CreditCard;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\Pix;

class PaymentMethodFactory
{
    public static function createCreditCard(
        ?string $token = null,
        ?string $holderName = null,
        ?int $expirationMonth = null,
        ?int $expirationYear = null,
        ?string $brand = null,
        ?string $lastFourDigits = null,
    ): CreditCard {
        return new CreditCard(
            token: $token,
            holderName: $holderName,
            expirationMonth: $expirationMonth,
            expirationYear: $expirationYear,
            brand: $brand,
            lastFourDigits: $lastFourDigits,
        );
    }

    public static function createPix(
        int $expiresAt,
    ): Pix {        
        return new Pix(
            expiresAt: $expiresAt,
        );
    }
}

<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects\PaymentMethod;

use DateTimeImmutable;

class PaymentMethodFactory
{
    public static function createCreditCard(
        string $token,
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
        int $expirationMinutes,
        ?string $pixKey = null,
    ): Pix {
        $expiresAt = (new DateTimeImmutable())->modify("+{$expirationMinutes} minutes");
        
        return new Pix(
            expiresAt: $expiresAt,
            pixKey: $pixKey,
        );
    }
}

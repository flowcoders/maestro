<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Factories;

use DateTimeImmutable;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\BankSlip;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\CreditCard;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\DebitCard;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\Pix;

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

    public static function createDebitCard(
        string $token,
        ?string $holderName = null,
        ?int $expirationMonth = null,
        ?int $expirationYear = null,
        ?string $brand = null,
        ?string $lastFourDigits = null,
    ): DebitCard {
        return new DebitCard(
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

    public static function createBankSlip(
        int $expirationDays,
        ?string $instructions = null,
    ): BankSlip {
        $expiresAt = (new DateTimeImmutable())->modify("+{$expirationDays} days");
        
        return new BankSlip(
            expiresAt: $expiresAt,
            instructions: $instructions,
        );
    }
}

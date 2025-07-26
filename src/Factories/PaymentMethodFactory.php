<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Factories;

use Flowcoders\Maestro\DTOs\PaymentMethods\CreditCardDTO;
use Flowcoders\Maestro\DTOs\PaymentMethods\PixDTO;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\CreditCard;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\Pix;

class PaymentMethodFactory
{
    public static function createPixFromDTO(PixDTO $pixDTO): Pix
    {
        return Pix::create($pixDTO->expiresAt);
    }

    public static function createCreditCardFromDTO(CreditCardDTO $cardDTO): CreditCard
    {
        return CreditCard::create(
            token: $cardDTO->token,
            bin: $cardDTO->bin,
            holderName: $cardDTO->holderName,
            expirationMonth: $cardDTO->expirationMonth,
            expirationYear: $cardDTO->expirationYear,
            brand: $cardDTO->brand,
            lastFourDigits: $cardDTO->lastFourDigits,
        );
    }
}

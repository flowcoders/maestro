<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs\PaymentMethods;

use Flowcoders\Maestro\Enums\CardBrand;
use Flowcoders\Maestro\Enums\PaymentMethod;
use Flowcoders\Maestro\Contracts\DTOs\PaymentMethodInterface;

readonly class CreditCardDTO implements PaymentMethodInterface
{
    public function __construct(
        public ?string $token = null,
        public ?string $bin = null,
        public ?string $holderName = null,
        public ?int $expirationMonth = null,
        public ?int $expirationYear = null,
        public ?CardBrand $brand = null,
        public ?string $lastFourDigits = null,
    ) {
    }

    public static function create(
        ?string $token = null,
        ?string $bin = null,
        ?string $holderName = null,
        ?int $expirationMonth = null,
        ?int $expirationYear = null,
        ?CardBrand $brand = null,
        ?string $lastFourDigits = null,
    ): self {
        return new self(
            token: $token,
            bin: $bin,
            holderName: $holderName,
            expirationMonth: $expirationMonth,
            expirationYear: $expirationYear,
            brand: $brand,
            lastFourDigits: $lastFourDigits,
        );
    }

    public function getType(): string
    {
        return PaymentMethod::CREDIT_CARD->value;
    }
}

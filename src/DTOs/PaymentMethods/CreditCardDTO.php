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

    public function getType(): string
    {
        return PaymentMethod::CREDIT_CARD->value;
    }
}

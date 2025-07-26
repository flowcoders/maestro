<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects\PaymentMethod;

readonly class DebitCard implements PaymentMethodInterface
{
    public function __construct(
        public string $token,
        public ?string $holderName = null,
        public ?int $expirationMonth = null,
        public ?int $expirationYear = null,
        public ?string $brand = null,
        public ?string $lastFourDigits = null,
    ) {}

    public function getType(): string
    {
        return 'debit_card';
    }

    public function isDocumentRequired(): bool
    {
        return false;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'token' => $this->token,
            'holder_name' => $this->holderName,
            'expiration_month' => $this->expirationMonth,
            'expiration_year' => $this->expirationYear,
            'brand' => $this->brand,
            'last_four_digits' => $this->lastFourDigits,
        ];
    }
}

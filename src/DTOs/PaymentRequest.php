<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\Contracts\PaymentMethodInterface;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\ValueObjects\Money;
use InvalidArgumentException;

readonly class PaymentRequest
{
    public Money $money;

    public function __construct(
        public int $amount,
        public Currency $currency,
        public PaymentMethodInterface $paymentMethod,
        public string $description,
        public Customer $customer,
        public int $installments = 1,
        public bool $capture = true,
        public ?string $token = null,
        public ?string $externalReference = null,
        public ?string $statementDescriptor = null,
        public ?string $notificationUrl = null,
        public ?string $callbackUrl = null,
        public ?array $metadata = null,
        public ?string $idempotencyKey = null,
    ) {
        $this->money = new Money($amount, $currency);
        $this->validateInstallments($installments);
    }

    private function validateInstallments(int $installments): void
    {
        if ($installments < 1) {
            throw new InvalidArgumentException('Installments must be at least 1');
        }

        if ($installments > 12) {
            throw new InvalidArgumentException('Installments cannot exceed 12');
        }
    }
}

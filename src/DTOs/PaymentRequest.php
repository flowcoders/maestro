<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Carbon\Carbon;
use Flowcoders\Maestro\Contracts\PaymentMethodInterface;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Utils\TimezoneHelper;
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
        public ?string $expiresAt = null,
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
        $this->validateExpiresAt();
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

    private function validateExpiresAt(): void
    {
        if ($this->expiresAt === null) {
            return;
        }

        try {
            TimezoneHelper::parse($this->expiresAt);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Invalid expiresAt format. Use ISO 8601 format (e.g., '2024-12-31T23:59:59Z' or '2024-12-31T23:59:59-03:00') or date format (e.g., '2024-12-31')");
        }
    }

    public function getExpiresAt(): ?Carbon
    {
        if ($this->expiresAt === null) {
            return null;
        }

        return TimezoneHelper::parse($this->expiresAt);
    }
}

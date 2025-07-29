<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\PaymentMethod;
use Flowcoders\Maestro\Contracts\PaymentMethodInterface;
use InvalidArgumentException;

readonly class Payment
{
    public function __construct(
        public int $amount,
        public Currency $currency,
        public string $description,
        public PaymentMethodInterface $paymentMethod,
        public Customer $customer,
        public int $installments = 1,
        public ?string $externalReference = null,
        public ?array $metadata = null,
        public ?string $notificationUrl = null,
        public ?string $callbackUrl = null,
    ) {
        $this->validateAmount($amount);
        $this->validateInstallments($installments);
        $this->validateCustomerRequirements();
    }

    private function validateAmount(int $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero');
        }

        // Maximum amount check (100,000.00 in cents)
        if ($amount > 10000000) {
            throw new InvalidArgumentException('Payment amount cannot exceed 100,000.00');
        }
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

    private function validateCustomerRequirements(): void
    {
        // PIX requires customer with valid document
        if ($this->paymentMethod->getType() === PaymentMethod::PIX->value) {
            if (!$this->customer->hasValidDocument()) {
                throw new InvalidArgumentException('PIX payments require customer with valid document');
            }
        }
    }

    public function getAmountInCurrency(): float
    {
        return $this->amount / 100; // Convert cents to currency units
    }

    public function requiresCustomer(): bool
    {
        return $this->paymentMethod->isDocumentRequired();
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'amount_in_currency' => $this->getAmountInCurrency(),
            'currency' => $this->currency->value,
            'description' => $this->description,
            'payment_method' => $this->paymentMethod->toArray(),
            'customer' => $this->customer->toArray(),
            'installments' => $this->installments,
            'external_reference' => $this->externalReference,
            'metadata' => $this->metadata,
            'notification_url' => $this->notificationUrl,
            'callback_url' => $this->callbackUrl,
        ];
    }
}

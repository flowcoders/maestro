<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use Flowcoders\Maestro\ValueObjects\Customer;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\PaymentMethod;
use Flowcoders\Maestro\Contracts\ValueObjects\PaymentMethodInterface;
use InvalidArgumentException;

readonly class Payment
{
    public function __construct(
        public int $amount,
        public Currency $currency,
        public string $description,
        public PaymentMethodInterface $paymentMethod,
        public int $installments = 1,
        public Customer $customer,
        public ?string $externalReference = null,
        public ?array $metadata = null,
        public ?string $notificationUrl = null,
        public ?string $callbackUrl = null,
    ) {
        $this->validateAmount($amount);
        $this->validateInstallments($installments);
        $this->validateCustomerRequirements();
    }

    public static function create(
        int $amount,
        Currency $currency,
        string $description,
        PaymentMethodInterface $paymentMethod,
        int $installments = 1,
        Customer $customer,
        ?string $externalReference = null,
        ?array $metadata = null,
        ?string $notificationUrl = null,
        ?string $callbackUrl = null,
    ): self {
        return new self(
            amount: $amount,
            currency: $currency,
            description: $description,
            paymentMethod: $paymentMethod,
            installments: $installments,
            customer: $customer,
            externalReference: $externalReference,
            metadata: $metadata,
            notificationUrl: $notificationUrl,
            callbackUrl: $callbackUrl,
        );
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
            if (!$this->customer) {
                throw new InvalidArgumentException('PIX payments require customer information');
            }

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
            'installments' => $this->installments,
            'customer' => $this->customer?->toArray(),
            'external_reference' => $this->externalReference,
            'metadata' => $this->metadata,
            'notification_url' => $this->notificationUrl,
            'callback_url' => $this->callbackUrl,
        ];
    }
}

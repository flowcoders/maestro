<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use Flowcoders\Maestro\DTOs\CustomerDTO;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\PaymentMethod;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\PaymentMethodInterface;
use InvalidArgumentException;

readonly class Payment
{
    public function __construct(
        public int $amount,
        public Currency $currency,
        public string $description,
        public PaymentMethodInterface $paymentMethod,
        public int $installments = 1,
        public ?CustomerDTO $customer = null,
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
        ?CustomerDTO $customer = null,
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
            throw new InvalidArgumentException("Invalid amount: {$amount}. Must be greater than 0");
        }
    }

    private function validateInstallments(int $installments): void
    {
        if ($installments <= 0) {
            throw new InvalidArgumentException("Invalid installments: {$installments}. Must be greater than 0");
        }

        // Only credit cards support installments > 1, other methods must use installments = 1
        if ($installments > 1 && ! in_array($this->paymentMethod->getType(), [PaymentMethod::CREDIT_CARD->value], true)) {
            throw new InvalidArgumentException("Installments > 1 are not supported for payment method: {$this->paymentMethod->getType()}. Use installments = 1.");
        }
    }

    private function validateCustomerRequirements(): void
    {
        // Some payment methods require customer document
        if ($this->paymentMethod->isDocumentRequired()) {
            if ($this->customer === null) {
                throw new InvalidArgumentException("Customer is required for payment method: {$this->paymentMethod->getType()}");
            }

            if ($this->customer->document === null) {
                throw new InvalidArgumentException("Customer document is required for payment method: {$this->paymentMethod->getType()}");
            }
        }
    }

    public function requiresDocument(): bool
    {
        return $this->paymentMethod->isDocumentRequired();
    }

    public function getPaymentMethodType(): string
    {
        return $this->paymentMethod->getType();
    }

    public function getAmountInCents(): int
    {
        return $this->amount;
    }

    public function getAmountInDecimal(): float
    {
        return $this->amount / 100;
    }

    public function hasCustomer(): bool
    {
        return $this->customer !== null;
    }

    public function hasExternalReference(): bool
    {
        return $this->externalReference !== null;
    }

    public function hasMetadata(): bool
    {
        return $this->metadata !== null && count($this->metadata) > 0;
    }

    public function isInstallmentPayment(): bool
    {
        return $this->installments > 1;
    }

    public function hasNotificationUrl(): bool
    {
        return $this->notificationUrl !== null;
    }

    public function hasCallbackUrl(): bool
    {
        return $this->callbackUrl !== null;
    }
}

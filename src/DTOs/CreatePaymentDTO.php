<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\PaymentMethodInterface;
use InvalidArgumentException;

readonly class CreatePaymentDTO
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

        // Credit cards can have installments, but other methods typically can't
        if ($installments > 1 && ! in_array($this->paymentMethod->getType(), ['credit_card'], true)) {
            throw new InvalidArgumentException("Installments are not supported for payment method: {$this->paymentMethod->getType()}");
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
}

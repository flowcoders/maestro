<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects\PaymentMethod;

use Flowcoders\Maestro\Enums\PaymentMethod;
use InvalidArgumentException;

readonly class CreditCard implements PaymentMethodInterface
{
    public function __construct(
        public string $token,
        public string $holderName,
        public int $expirationMonth,
        public int $expirationYear,
        public string $brand,
        public ?string $lastFourDigits = null,
    ) {
        $this->validateToken($token);
        $this->validateHolderName($holderName);
        $this->validateExpirationMonth($expirationMonth);
        $this->validateExpirationYear($expirationYear);
        $this->validateBrand($brand);
    }

    private function validateToken(string $token): void
    {
        if (empty(trim($token))) {
            throw new InvalidArgumentException('Credit card token cannot be empty');
        }
    }

    private function validateHolderName(string $holderName): void
    {
        if (empty(trim($holderName))) {
            throw new InvalidArgumentException('Credit card holder name cannot be empty');
        }

        if (strlen($holderName) < 2) {
            throw new InvalidArgumentException('Credit card holder name must be at least 2 characters');
        }
    }

    private function validateExpirationMonth(int $month): void
    {
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException("Invalid expiration month: {$month}. Must be between 1 and 12");
        }
    }

    private function validateExpirationYear(int $year): void
    {
        $currentYear = (int) date('Y');
        
        if ($year < $currentYear) {
            throw new InvalidArgumentException("Expiration year cannot be in the past: {$year}");
        }

        if ($year > $currentYear + 20) {
            throw new InvalidArgumentException("Expiration year is too far in the future: {$year}");
        }
    }

    private function validateBrand(string $brand): void
    {
        $validBrands = ['visa', 'mastercard', 'amex', 'elo', 'hipercard', 'diners'];
        $normalizedBrand = strtolower($brand);
        
        if (! in_array($normalizedBrand, $validBrands, true)) {
            throw new InvalidArgumentException("Invalid credit card brand: {$brand}");
        }
    }

    public function getType(): string
    {
        return PaymentMethod::CREDIT_CARD->value;
    }

    public function isDocumentRequired(): bool
    {
        return false; // Credit cards typically don't require document
    }

    public function isExpired(): bool
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');

        if ($this->expirationYear < $currentYear) {
            return true;
        }

        if ($this->expirationYear === $currentYear && $this->expirationMonth < $currentMonth) {
            return true;
        }

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
            'brand' => strtolower($this->brand),
            'last_four_digits' => $this->lastFourDigits,
        ];
    }
}

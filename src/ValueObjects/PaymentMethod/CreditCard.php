<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects\PaymentMethod;

use Flowcoders\Maestro\Contracts\PaymentMethodInterface;
use Flowcoders\Maestro\Enums\CardBrand;
use Flowcoders\Maestro\Enums\PaymentMethod;
use InvalidArgumentException;

readonly class CreditCard implements PaymentMethodInterface
{
    public function __construct(
        public ?string $token = null,
        public ?string $number = null,
        public ?string $bin = null,
        public ?string $holderName = null,
        public ?int $expiryMonth = null,
        public ?int $expiryYear = null,
        public ?CardBrand $brand = null,
        public ?string $lastFourDigits = null,
        public ?string $cvv = null,
    ) {
        $this->validateToken($token);
        $this->validateNumber($number);
        $this->validateBin($bin);
        $this->validateHolderName($holderName);
        $this->validateExpiryMonth($expiryMonth);
        $this->validateExpiryYear($expiryYear);
        $this->validateCVV($cvv);
    }

    private function validateToken(?string $token): void
    {
        if (is_null($token)) {
            return;
        }

        if (empty(trim($token))) {
            throw new InvalidArgumentException('Credit card token cannot be empty');
        }
    }

    private function validateNumber(?string $number): void
    {
        if (is_null($number)) {
            return;
        }

        if (empty(trim($number))) {
            throw new InvalidArgumentException('Credit card number cannot be empty');
        }

        if (!is_numeric($number) || strlen($number) < 12 || strlen($number) > 19) {
            throw new InvalidArgumentException('Credit card number must be a number between 12 and 19 digits');
        }
    }

    private function validateBin(?string $bin): void
    {
        if (is_null($bin)) {
            return;
        }

        if (empty(trim($bin))) {
            throw new InvalidArgumentException('Credit card bin cannot be empty');
        }

        if (!is_numeric($bin) || strlen($bin) < 6 || strlen($bin) > 8) {
            throw new InvalidArgumentException('Credit card bin must be a number between 6 and 8 digits');
        }
    }

    private function validateHolderName(?string $holderName): void
    {
        if (is_null($holderName)) {
            return;
        }

        if (empty(trim($holderName))) {
            throw new InvalidArgumentException('Credit card holder name cannot be empty');
        }

        if (strlen($holderName) < 2) {
            throw new InvalidArgumentException('Credit card holder name must be at least 2 characters');
        }
    }

    private function validateExpiryMonth(?int $month): void
    {
        if (is_null($month)) {
            return;
        }

        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException("Invalid expiration month: {$month}. Must be between 1 and 12");
        }
    }

    private function validateExpiryYear(?int $year): void
    {
        if (is_null($year)) {
            return;
        }

        $currentYear = (int) date('Y');

        if ($year < $currentYear) {
            throw new InvalidArgumentException("Expiration year cannot be in the past: {$year}");
        }

        if ($year > $currentYear + 20) {
            throw new InvalidArgumentException("Expiration year is too far in the future: {$year}");
        }
    }

    private function validateCVV(?string $cvv): void
    {
        if (is_null($cvv)) {
            return;
        }

        if (empty(trim($cvv))) {
            throw new InvalidArgumentException('Credit card CVV cannot be empty');
        }

        if (!is_numeric($cvv) || strlen($cvv) < 3 || strlen($cvv) > 4) {
            throw new InvalidArgumentException('Credit card CVV must be a number between 3 and 4 digits');
        }
    }

    public function getType(): string
    {
        return PaymentMethod::CREDIT_CARD->value;
    }

    public function isDocumentRequired(): bool
    {
        return false;
    }

    public function isExpired(): bool
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');

        if ($this->expiryYear < $currentYear) {
            return true;
        }

        if ($this->expiryYear === $currentYear && $this->expiryMonth < $currentMonth) {
            return true;
        }

        return false;
    }
}

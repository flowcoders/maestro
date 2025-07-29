<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects\PaymentMethod;

use Flowcoders\Maestro\Contracts\ValueObjects\PaymentMethodInterface;
use Flowcoders\Maestro\Enums\CardBrand;
use Flowcoders\Maestro\Enums\PaymentMethod;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

readonly class CreditCard implements Arrayable, PaymentMethodInterface
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
        $this->validateToken($token);
        $this->validateHolderName($holderName);
        $this->validateExpirationMonth($expirationMonth);
        $this->validateExpirationYear($expirationYear);
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

    private function validateExpirationMonth(?int $month): void
    {
        if (is_null($month)) {
            return;
        }

        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException("Invalid expiration month: {$month}. Must be between 1 and 12");
        }
    }

    private function validateExpirationYear(?int $year): void
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
            'bin' => $this->bin,
            'holder_name' => $this->holderName,
            'expiration_month' => $this->expirationMonth,
            'expiration_year' => $this->expirationYear,
            'brand' => $this->brand?->value,
            'last_four_digits' => $this->lastFourDigits,
        ];
    }
}

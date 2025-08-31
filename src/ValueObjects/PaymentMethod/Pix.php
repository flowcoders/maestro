<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects\PaymentMethod;

use Carbon\CarbonImmutable;
use Flowcoders\Maestro\Contracts\PaymentMethodInterface;
use Flowcoders\Maestro\Enums\PaymentMethod;
use InvalidArgumentException;

readonly class Pix implements PaymentMethodInterface
{
    public function __construct(
        public ?string $expiresAt = null,
        public ?string $qrCode = null,
        public ?string $qrCodeBase64 = null,
        public ?string $qrCodeUrl = null,
        public ?string $qrCodeImage = null,
        public ?string $qrCodeImageBase64 = null,
    ) {
        $this->validateExpiresAt();
    }

    public function getExpiresAt(): CarbonImmutable
    {
        if ($this->expiresAt === null) {
            // Default to 1 hour from now if not specified
            return CarbonImmutable::now()->addHour();
        }

        return CarbonImmutable::parse($this->expiresAt);
    }

    private function validateExpiresAt(): void
    {
        if ($this->expiresAt === null) {
            return; // Valid - will use default 1 hour
        }

        try {
            $expiresDate = CarbonImmutable::parse($this->expiresAt);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Invalid expiresAt format. Use ISO 8601 format (e.g., '2024-12-31T23:59:59Z' or '2024-12-31T23:59:59-03:00')");
        }

        $now = CarbonImmutable::now();

        if ($expiresDate <= $now) {
            throw new InvalidArgumentException('PIX expiration date must be in the future');
        }

        // PIX payments typically expire within 24 hours
        $maxExpirationTime = $now->addHours(24);
        if ($expiresDate > $maxExpirationTime) {
            throw new InvalidArgumentException('PIX expiration date cannot be more than 24 hours in the future');
        }
    }

    public function getType(): string
    {
        return PaymentMethod::PIX->value;
    }

    public function isDocumentRequired(): bool
    {
        return true;
    }

    public function isExpired(): bool
    {
        return $this->getExpiresAt() <= CarbonImmutable::now();
    }

    public function getExpirationTimestamp(): int
    {
        return $this->getExpiresAt()->getTimestamp();
    }
}

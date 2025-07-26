<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects\PaymentMethod;

use DateTimeImmutable;
use InvalidArgumentException;
use Flowcoders\Maestro\Enums\PaymentMethod;

readonly class Pix implements PaymentMethodInterface
{
    public function __construct(
        public DateTimeImmutable $expiresAt,
        public ?string $pixKey = null,
    ) {
        $this->validateExpiresAt($expiresAt);
    }

    private function validateExpiresAt(DateTimeImmutable $expiresAt): void
    {
        $now = new DateTimeImmutable();
        
        if ($expiresAt <= $now) {
            throw new InvalidArgumentException('PIX expiration date must be in the future');
        }

        // PIX payments typically expire within 24 hours
        $maxExpirationTime = $now->modify('+24 hours');
        if ($expiresAt > $maxExpirationTime) {
            throw new InvalidArgumentException('PIX expiration date cannot be more than 24 hours in the future');
        }
    }

    public function getType(): string
    {
        return PaymentMethod::PIX->value;
    }

    public function isDocumentRequired(): bool
    {
        return true; // PIX requires document for validation
    }

    public function isExpired(): bool
    {
        return $this->expiresAt <= new DateTimeImmutable();
    }

    public function getExpirationTimestamp(): int
    {
        return $this->expiresAt->getTimestamp();
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'expires_at' => $this->expiresAt->format('Y-m-d H:i:s'),
            'expires_at_timestamp' => $this->getExpirationTimestamp(),
            'pix_key' => $this->pixKey,
        ];
    }
}

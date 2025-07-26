<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects\PaymentMethod;

use DateTimeImmutable;
use InvalidArgumentException;

readonly class BankSlip implements PaymentMethodInterface
{
    public function __construct(
        public DateTimeImmutable $expiresAt,
        public ?string $instructions = null,
    ) {
        $this->validateExpiration($expiresAt);
    }

    private function validateExpiration(DateTimeImmutable $expiresAt): void
    {
        $now = new DateTimeImmutable();
        $maxExpiration = $now->modify('+30 days');

        if ($expiresAt <= $now) {
            throw new InvalidArgumentException('Bank slip expiration date must be in the future');
        }

        if ($expiresAt > $maxExpiration) {
            throw new InvalidArgumentException('Bank slip expiration date cannot be more than 30 days from now');
        }
    }

    public function getType(): string
    {
        return 'bank_slip';
    }

    public function isDocumentRequired(): bool
    {
        return true;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'expires_at' => $this->expiresAt->format('Y-m-d\TH:i:s\Z'),
            'instructions' => $this->instructions,
        ];
    }

    public function isExpired(): bool
    {
        return $this->expiresAt <= new DateTimeImmutable();
    }

    public function getExpirationDays(): int
    {
        $now = new DateTimeImmutable();
        $diff = $now->diff($this->expiresAt);
        
        return $diff->days;
    }
}

<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects\PaymentMethod;

use Carbon\Carbon;
use Flowcoders\Maestro\Contracts\PaymentMethodInterface;
use Flowcoders\Maestro\Enums\PaymentMethod;
use InvalidArgumentException;

readonly class Pix implements PaymentMethodInterface
{
    public function __construct(
        public int $expiresAt = 60,
    ) {
        $this->validateExpiresAt();
    }

    private function getExpiresAt(): Carbon
    {
        return Carbon::now()->addMinutes($this->expiresAt);
    }

    private function validateExpiresAt(): void
    {
        $expiresDate = $this->getExpiresAt();
        $now = Carbon::now();

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
        return $this->getExpiresAt() <= Carbon::now();
    }

    public function getExpirationTimestamp(): int
    {
        return $this->getExpiresAt()->getTimestamp();
    }
}

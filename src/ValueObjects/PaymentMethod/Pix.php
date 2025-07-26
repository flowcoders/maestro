<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects\PaymentMethod;

use DateTimeImmutable;
use Flowcoders\Maestro\Contracts\ValueObjects\PaymentMethodInterface;
use Flowcoders\Maestro\Enums\PaymentMethod;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

readonly class Pix implements Arrayable, PaymentMethodInterface
{
    public function __construct(
        public int $expiresAt = 60,
    ) {
        $this->validateExpiresAt($expiresAt);
    }

    public static function create(int $expiresAt): self
    {
        return new self(
            expiresAt: $expiresAt,
        );
    }

    private function getExpiresAt(): DateTimeImmutable
    {
        return (new DateTimeImmutable())->modify("+{$this->expiresAt} minutes");
    }

    private function validateExpiresAt(int $expiresAt): void
    {
        $expiresDate = $this->getExpiresAt();
        $now = new DateTimeImmutable();

        if ($expiresDate <= $now) {
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
        return $this->getExpiresAt() <= new DateTimeImmutable();
    }

    public function getExpirationTimestamp(): int
    {
        return $this->getExpiresAt()->getTimestamp();
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'expires_at' => $this->getExpiresAt()->format('Y-m-d H:i:s'),
            'expires_at_timestamp' => $this->getExpirationTimestamp(),
        ];
    }
}

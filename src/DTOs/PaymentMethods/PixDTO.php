<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs\PaymentMethods;

use Flowcoders\Maestro\Enums\PaymentMethod;
use Flowcoders\Maestro\Contracts\DTOs\PaymentMethodInterface;

readonly class PixDTO implements PaymentMethodInterface
{
    public function __construct(
        public int $expiresAt = 60,
    ) {}

    public static function create(int $expiresAt): self
    {
        return new self(
            expiresAt: $expiresAt,
        );
    }

    public function getType(): string
    {
        return PaymentMethod::PIX->value;
    }
}

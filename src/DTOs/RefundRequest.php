<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\ValueObjects\Money;

readonly class RefundRequest
{
    public function __construct(
        public string $paymentId,
        public ?Money $money = null,
        public ?string $reason = null,
        public ?array $metadata = null,
        public ?string $idempotencyKey = null,
    ) {
    }
}

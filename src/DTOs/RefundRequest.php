<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

readonly class RefundRequest
{
    public function __construct(
        public string $paymentId,
        public ?int $amount = null,
        public ?string $reason = null,
        public ?array $metadata = null,
        public ?string $idempotencyKey = null,
    ) {
    }
}

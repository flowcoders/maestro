<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

readonly class RefundPaymentDTO
{
    public function __construct(
        public string $paymentId,
        public ?int $amount = null,
        public ?string $reason = null,
        public ?array $metadata = null,
    ) {}
}

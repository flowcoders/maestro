<?php

namespace Flowcoders\Maestro\DTOs;

use Carbon\CarbonImmutable;
use Flowcoders\Maestro\Enums\RefundStatus;

readonly class RefundResponse
{
    public function __construct(
        public string $id,
        public string $paymentId,
        public int $amount,
        public RefundStatus $status,
        public ?string $reason = null,
        public ?array $metadata = null,
        public ?array $pspResponse = null,
        public ?string $error = null,
        public ?string $errorCode = null,
        public ?CarbonImmutable $createdAt = null,
    ) {
    }
}

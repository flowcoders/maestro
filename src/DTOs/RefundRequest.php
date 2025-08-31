<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\ValueObjects\Money;

readonly class RefundRequest
{
    public ?Money $money;

    public function __construct(
        public string $paymentId,
        public ?int $amount = null,
        public ?Currency $currency = null,
        public ?string $reason = null,
        public ?array $metadata = null,
        public ?string $idempotencyKey = null,
    ) {
        $this->money = ($amount !== null && $currency !== null)
            ? new Money($amount, $currency)
            : null;
    }
}

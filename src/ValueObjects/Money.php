<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use Flowcoders\Maestro\Enums\Currency;
use InvalidArgumentException;

readonly class Money
{
    public function __construct(
        public int $amount,
        public Currency $currency,
    ) {
        $this->validateAmount($amount);
    }

    private function validateAmount(int $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Payment amount must be greater or equal to zero');
        }

        // Maximum amount check (100,000.00 in cents)
        if ($amount > 10000000) {
            throw new InvalidArgumentException('Payment amount cannot exceed 100,000.00');
        }
    }
}

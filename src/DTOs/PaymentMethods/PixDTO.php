<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs\PaymentMethods;

readonly class PixDTO
{
    public function __construct(
        public int $expiresAt = 60,
    ) {
    }


}

<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\Enums\Currency;

readonly class CreatePaymentDTO
{
    public function __construct(
        public int $amount,
        public Currency $currency,
        public string $description,
        public int $installments = 1,
        public ?CustomerDTO $customer = null,
        public ?string $paymentMethod = null,
        public ?string $externalReference = null,
        public ?array $metadata = null,
        public ?string $notificationUrl = null,
        public ?string $callbackUrl = null,
    ) {
    }
}

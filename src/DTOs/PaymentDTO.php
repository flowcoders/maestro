<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Contracts\DTOs\PaymentMethodInterface;

readonly class PaymentDTO
{
    public function __construct(
        public int $amount,
        public Currency $currency,
        public string $description,
        public PaymentMethodInterface $paymentMethod,
        public int $installments = 1,
        public CustomerDTO $customer,
        public ?string $externalReference = null,
        public ?array $metadata = null,
        public ?string $notificationUrl = null,
        public ?string $callbackUrl = null,
    ) {
    }
}

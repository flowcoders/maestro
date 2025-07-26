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
        public ?CustomerDTO $customer = null,
        public ?string $externalReference = null,
        public ?array $metadata = null,
        public ?string $notificationUrl = null,
        public ?string $callbackUrl = null,
    ) {}

    public static function create(
        int $amount,
        Currency $currency,
        string $description,
        PaymentMethodInterface $paymentMethod,
        int $installments = 1,
        ?CustomerDTO $customer = null,
        ?string $externalReference = null,
        ?array $metadata = null,
        ?string $notificationUrl = null,
        ?string $callbackUrl = null,
    ): self {
        return new self(
            amount: $amount,
            currency: $currency,
            description: $description,
            paymentMethod: $paymentMethod,
            installments: $installments,
            customer: $customer,
            externalReference: $externalReference,
            metadata: $metadata,
            notificationUrl: $notificationUrl,
            callbackUrl: $callbackUrl,
        );
    }
}

<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Carbon\CarbonImmutable;
use Flowcoders\Maestro\Contracts\PaymentMethodInterface;
use Flowcoders\Maestro\Enums\PaymentStatus;
use Flowcoders\Maestro\ValueObjects\Money;

readonly class PaymentResponse
{
    public function __construct(
        public string $id,
        public PaymentStatus $status,
        public Money $money,
        public ?string $description = null,
        public ?Customer $customer = null,
        public ?string $externalReference = null,
        public ?PaymentMethodInterface $paymentMethod = null,
        public ?bool $capture = null,
        public ?string $statementDescriptor = null,
        public ?int $installments = null,
        public ?string $notificationUrl = null,
        public ?array $metadata = null,
        public ?array $pspResponse = null,
        public ?string $error = null,
        public ?string $errorCode = null,
        public ?CarbonImmutable $createdAt = null,
        public ?CarbonImmutable $updatedAt = null,
    ) {
    }
}

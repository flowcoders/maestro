<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use DateTimeImmutable;
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
        public ?string $paymentMethod = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public ?array $metadata = null,
        public ?array $pspResponse = null,
        public ?string $error = null,
        public ?string $errorCode = null,
    ) {
    }
}

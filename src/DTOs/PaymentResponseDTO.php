<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\PaymentStatus;
use DateTimeImmutable;

readonly class PaymentResponseDTO
{
    public function __construct(
        public string $id,
        public PaymentStatus $status,
        public int $amount,
        public Currency $currency,
        public ?string $description = null,
        public ?CustomerDTO $customer = null,
        public ?string $externalReference = null,
        public ?string $paymentMethodId = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public ?array $metadata = null,
        public ?array $pspResponse = null,
        public ?string $error = null,
        public ?string $errorCode = null,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->error === null;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }
}

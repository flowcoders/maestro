<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects\PaymentMethod;

use Flowcoders\Maestro\Contracts\PaymentMethodInterface;
use Flowcoders\Maestro\Enums\PaymentMethod;

readonly class Pix implements PaymentMethodInterface
{
    public function __construct(
        public ?string $qrCode = null,
        public ?string $qrCodeBase64 = null,
        public ?string $qrCodeUrl = null,
        public ?string $qrCodeImage = null,
        public ?string $qrCodeImageBase64 = null,
    ) {
    }

    public function getType(): string
    {
        return PaymentMethod::PIX->value;
    }

    public function isDocumentRequired(): bool
    {
        return true;
    }
}

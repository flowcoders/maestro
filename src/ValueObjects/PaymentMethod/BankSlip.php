<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects\PaymentMethod;

use Flowcoders\Maestro\Contracts\PaymentMethodInterface;
use Flowcoders\Maestro\Enums\PaymentMethod;

readonly class BankSlip implements PaymentMethodInterface
{
    public function __construct(
        public ?string $bankSlipUrl = null,
        public ?string $digitableLine = null,
        public ?string $barcode = null,
        public ?int $daysAfterDueDateToExpire = 30,
    ) {
    }

    public function getType(): string
    {
        return PaymentMethod::BANK_SLIP->value;
    }

    public function isDocumentRequired(): bool
    {
        return true;
    }
}

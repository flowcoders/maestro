<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects\PaymentMethod;

interface PaymentMethodInterface
{
    public function getType(): string;
    public function isDocumentRequired(): bool;
    public function toArray(): array;
}

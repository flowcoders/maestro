<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Contracts;

interface PaymentMethodInterface
{
    public function getType(): string;
    public function isDocumentRequired(): bool;
}

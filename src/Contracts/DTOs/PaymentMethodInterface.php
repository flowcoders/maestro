<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Contracts\DTOs;

interface PaymentMethodInterface
{
    public function getType(): string;
}

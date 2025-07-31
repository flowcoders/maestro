<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use InvalidArgumentException;

readonly class Email
{
    public function __construct(
        public string $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (! filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$this->value}");
        }
    }
}

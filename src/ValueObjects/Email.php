<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use InvalidArgumentException;
use Stringable;

readonly class Email implements Stringable
{
    public function __construct(
        private string $value
    ) {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$value}");
        }
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}

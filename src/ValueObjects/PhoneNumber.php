<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use InvalidArgumentException;
use Stringable;

readonly class PhoneNumber implements Stringable
{
    public function __construct(
        private string $value
    ) {
        if (! $this->isValid($value)) {
            throw new InvalidArgumentException("Invalid phone number format: {$value}");
        }
    }

    private function isValid(string $phone): bool
    {
        // Brazilian phone format validation
        return (bool) preg_match('/^\+?55\s?\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/', $phone);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function equals(PhoneNumber $other): bool
    {
        return $this->value === $other->value;
    }

    public function normalized(): string
    {
        return preg_replace('/[^\d+]/', '', $this->value);
    }

    public function getAreaCode(): string
    {
        preg_match('/\(?(\d{2})\)?/', $this->value, $matches);
        
        return $matches[1] ?? '';
    }
}

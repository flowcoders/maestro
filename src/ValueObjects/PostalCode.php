<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use Flowcoders\Maestro\Enums\Country;
use InvalidArgumentException;
use Stringable;

readonly class PostalCode implements Stringable
{
    public function __construct(
        private string $value,
        private Country $country = Country::BR
    ) {
        if (! $this->isValid($value, $country)) {
            throw new InvalidArgumentException("Invalid postal code for {$country->value}: {$value}");
        }
    }

    private function isValid(string $code, Country $country): bool
    {
        return match ($country) {
            Country::BR => $this->isValidBrazilianPostalCode($code),
            Country::AR => $this->isValidArgentinianPostalCode($code),
            // Add more countries as needed
        };
    }

    private function isValidBrazilianPostalCode(string $code): bool
    {
        return (bool) preg_match('/^\d{5}-?\d{3}$/', $code);
    }

    private function isValidArgentinianPostalCode(string $code): bool
    {
        return (bool) preg_match('/^[A-Z]\d{4}[A-Z]{3}$/', $code);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function formatted(): string
    {
        return match ($this->country) {
            Country::BR => $this->formatBrazilian(),
            Country::AR => $this->value, // Already formatted
        };
    }

    private function formatBrazilian(): string
    {
        $normalized = preg_replace('/[^0-9]/', '', $this->value);
        
        return substr($normalized, 0, 5) . '-' . substr($normalized, 5, 3);
    }

    public function equals(PostalCode $other): bool
    {
        return $this->value === $other->value && $this->country === $other->country;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }
}

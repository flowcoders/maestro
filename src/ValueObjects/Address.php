<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use Flowcoders\Maestro\Enums\CountryCode;
use InvalidArgumentException;

readonly class Address
{
    public function __construct(
        public string $postalCode,
        public string $streetLine1,
        public string $city,
        public string $stateOrProvince,
        public CountryCode $countryCode,
        public ?string $streetLine2 = null,
        public ?string $neighborhood = null,
        public ?string $complement = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->streetLine1) || strlen($this->streetLine1) < 2) {
            throw new InvalidArgumentException('Street line 1 must be at least 2 characters.');
        }

        if (empty($this->city)) {
            throw new InvalidArgumentException('City cannot be empty.');
        }

        if (empty($this->stateOrProvince)) {
            throw new InvalidArgumentException('State or province cannot be empty.');
        }

        // Optional: allow postal code to be empty for countries without postal systems
        if (!empty($this->postalCode) && strlen($this->postalCode) < 3) {
            throw new InvalidArgumentException('Postal code seems too short.');
        }
    }

    public function formatted(): string
    {
        $parts = array_filter([
            $this->streetLine1,
            $this->streetLine2,
            "{$this->city}" . ($this->stateOrProvince ? ", {$this->stateOrProvince}" : ''),
            $this->postalCode,
            $this->countryCode->value,
        ]);

        return implode(', ', $parts);
    }
}

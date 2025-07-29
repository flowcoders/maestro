<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use Flowcoders\Maestro\Enums\Country;
use Illuminate\Contracts\Support\Arrayable;

readonly class Address implements Arrayable
{
    public function __construct(
        public PostalCode $postalCode,
        public ?string $streetName = null,
        public ?string $streetNumber = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?Country $country = null,
        public ?string $neighborhood = null,
        public ?string $complement = null,
    ) {
    }

    public static function create(
        string $postalCode,
        ?string $streetName = null,
        ?string $streetNumber = null,
        ?string $city = null,
        ?string $state = null,
        ?Country $country = null,
        ?string $neighborhood = null,
        ?string $complement = null,
    ): self {
        return new self(
            postalCode: new PostalCode($postalCode),
            streetName: $streetName,
            streetNumber: $streetNumber,
            city: $city,
            state: $state,
            country: $country,
            neighborhood: $neighborhood,
            complement: $complement,
        );
    }

    public function toArray(): array
    {
        return [
            'postal_code' => $this->getPostalCodeString(),
            'street_name' => $this->streetName,
            'street_number' => $this->streetNumber,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country->value,
            'neighborhood' => $this->neighborhood,
            'complement' => $this->complement,
        ];
    }

    public function toString(): string
    {
        return $this->postalCode . ' ' . $this->streetName . ' ' . $this->streetNumber . ' ' . $this->city . ' ' . $this->state . ' ' . $this->country->value . ' ' . $this->neighborhood . ' ' . $this->complement;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function getPostalCodeString(): string
    {
        return $this->postalCode->toString();
    }
}

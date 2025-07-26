<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

readonly class Address implements Arrayable
{
    public function __construct(
        public string $postalCode,
        public ?string $streetName = null,
        public ?string $streetNumber = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $neighborhood = null,
    ) {
    }

    public static function create(
        string $postalCode,
        ?string $streetName = null,
        ?string $streetNumber = null,
        ?string $city = null,
        ?string $state = null,
        ?string $neighborhood = null,
    ): self {
        return new self(
            postalCode: $postalCode,
            streetName: $streetName,
            streetNumber: $streetNumber,
            city: $city,
            state: $state,
            neighborhood: $neighborhood,
        );
    }

    public function toArray(): array
    {
        return [
            'postal_code' => $this->postalCode,
            'street_name' => $this->streetName,
            'street_number' => $this->streetNumber,
            'city' => $this->city,
            'state' => $this->state,
            'neighborhood' => $this->neighborhood,
        ];
    }
}

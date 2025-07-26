<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

readonly class AddressDTO
{
    public function __construct(
        public ?string $streetName = null,
        public ?string $streetNumber = null,
        public ?string $postalCode = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $country = null,
        public ?string $neighborhood = null,
        public ?string $complement = null,
    ) {
    }
}

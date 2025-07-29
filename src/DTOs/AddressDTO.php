<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\Enums\Country;

readonly class AddressDTO
{
    public function __construct(
        public string $postalCode,
        public ?string $streetName = null,
        public ?string $streetNumber = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?Country $country = null,
        public ?string $neighborhood = null,
        public ?string $complement = null,
    ) {
    }
}

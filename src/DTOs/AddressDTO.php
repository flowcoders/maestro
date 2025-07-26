<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\Enums\Country;
use Flowcoders\Maestro\Concerns\ValidatesFormats;

readonly class AddressDTO
{
    use ValidatesFormats;

    public readonly string $postalCode;

    public function __construct(
        string $postalCode,
        public ?string $streetName = null,
        public ?string $streetNumber = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?Country $country = null,
        public ?string $neighborhood = null,
        public ?string $complement = null,
    ) {
        $this->validatePostalCode($postalCode);
        $this->postalCode = $this->normalizePostalCode($postalCode);
    }
}

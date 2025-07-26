<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\Enums\Country;
use Flowcoders\Maestro\ValueObjects\PostalCode;
use Flowcoders\Maestro\ValueObjects\ValueObjectFactory;
readonly class AddressDTO
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
            postalCode: ValueObjectFactory::createPostalCode($postalCode),
            streetName: $streetName,
            streetNumber: $streetNumber,
            city: $city,
            state: $state,
            country: $country,
            neighborhood: $neighborhood,
            complement: $complement,
        );
    }
}

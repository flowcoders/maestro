<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Factories;

use Flowcoders\Maestro\DTOs\AddressDTO;
use Flowcoders\Maestro\ValueObjects\Address;

class AddressFactory
{
    public static function fromDTO(AddressDTO $addressDTO): Address
    {
        return Address::create(
            postalCode: $addressDTO->postalCode,
            streetName: $addressDTO->streetName,
            streetNumber: $addressDTO->streetNumber,
            city: $addressDTO->city,
            state: $addressDTO->state,
            country: $addressDTO->country,
            neighborhood: $addressDTO->neighborhood,
            complement: $addressDTO->complement,
        );
    }

    public static function toDTO(Address $address): AddressDTO
    {
        return new AddressDTO(
            postalCode: $address->getPostalCodeString(),
            streetName: $address->streetName,
            streetNumber: $address->streetNumber,
            city: $address->city,
            state: $address->state,
            country: $address->country,
            neighborhood: $address->neighborhood,
            complement: $address->complement,
        );
    }
}

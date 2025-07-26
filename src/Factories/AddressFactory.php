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
            neighborhood: $addressDTO->neighborhood,
        );
    }

    public static function toDTO(Address $address): AddressDTO
    {
        return AddressDTO::create(
            postalCode: $address->postalCode,
            streetName: $address->streetName,
            streetNumber: $address->streetNumber,
            city: $address->city,
            state: $address->state,
            neighborhood: $address->neighborhood,
        );
    }
}

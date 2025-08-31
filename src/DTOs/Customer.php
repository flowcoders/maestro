<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\Enums\DocumentType;
use Flowcoders\Maestro\ValueObjects\Address;
use Flowcoders\Maestro\ValueObjects\Document;
use Flowcoders\Maestro\ValueObjects\Phone;

readonly class Customer
{
    public ?Document $document;
    public ?Phone $phone;
    public ?Address $address;

    public function __construct(
        public ?string $id = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        ?DocumentType $documentType = null,
        ?string $documentValue = null,
        ?string $phoneNumber = null,
        ?string $postalCode = null,
        ?string $streetLine1 = null,
        ?string $streetLine2 = null,
        ?string $city = null,
        ?string $stateOrProvince = null,
        ?string $countryCode = null,
        ?string $neighborhood = null,
    ) {
        // Create Document VO internally if data is provided
        $this->document = ($documentType !== null && $documentValue !== null)
            ? new Document($documentType, $documentValue)
            : null;

        // Create Phone VO internally if phone number is provided
        $this->phone = $phoneNumber !== null
            ? new Phone($phoneNumber)
            : null;

        // Create Address VO internally if address data is provided
        $this->address = $this->createAddressIfNeeded(
            $postalCode,
            $streetLine1,
            $streetLine2,
            $city,
            $stateOrProvince,
            $countryCode,
            $neighborhood
        );
    }

    private function createAddressIfNeeded(
        ?string $postalCode,
        ?string $streetLine1,
        ?string $streetLine2,
        ?string $city,
        ?string $stateOrProvince,
        ?string $countryCode,
        ?string $neighborhood
    ): ?Address {
        // Only create address if we have essential data
        if ($city === null && $postalCode === null) {
            return null;
        }

        return new Address(
            postalCode: $postalCode,
            streetLine1: $streetLine1,
            streetLine2: $streetLine2,
            city: $city,
            stateOrProvince: $stateOrProvince,
            countryCode: $countryCode ? \Flowcoders\Maestro\Enums\CountryCode::from($countryCode) : null,
            neighborhood: $neighborhood
        );
    }
}

<?php

namespace Flowcoders\Maestro\ValueObjects;

use Flowcoders\Maestro\Enums\DocumentType;
use Flowcoders\Maestro\ValueObjects\Address;

readonly class Customer
{
    public function __construct(
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $document = null,
        public ?DocumentType $documentType = null,
        public ?string $phone = null,
        public ?Address $address = null,
    ) {}

    public static function create(
        ?string $email = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $document = null,
        ?DocumentType $documentType = null,
        ?string $phone = null,
        ?Address $address = null,
    ): self {
        return new self(
            email: $email,
            firstName: $firstName,
            lastName: $lastName,
            document: $document,
            documentType: $documentType,
            phone: $phone,
            address: $address,
        );
    }
}

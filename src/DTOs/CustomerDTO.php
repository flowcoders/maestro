<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\Enums\DocumentType;

readonly class CustomerDTO
{
    public function __construct(
        public ?string $id = null,
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $document = null,
        public ?DocumentType $documentType = null,
        public ?string $phone = null,
        public ?AddressDTO $address = null,
    ) {
    }

    public static function create(
        ?string $id = null,
        ?string $email = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $document = null,
        ?DocumentType $documentType = null,
        ?string $phone = null,
        ?AddressDTO $address = null,
    ): self {
        return new self(
            id: $id,
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

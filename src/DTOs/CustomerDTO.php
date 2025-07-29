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
}

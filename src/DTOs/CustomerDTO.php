<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

readonly class CustomerDTO
{
    public function __construct(
        public ?string $id = null,
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $document = null,
        public ?string $documentType = null,
        public ?string $phone = null,
        public ?AddressDTO $address = null,
    ) {
    }

    public function getFullName(): string
    {
        return trim("{$this->firstName} {$this->lastName}");
    }
}

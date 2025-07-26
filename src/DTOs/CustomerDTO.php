<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\Enums\DocumentType;
use Flowcoders\Maestro\Concerns\ValidatesFormats;

readonly class CustomerDTO
{
    use ValidatesFormats;

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
        $this->validateEmail($email);
        $this->validatePhoneNumber($phone);
        
        if ($documentType === DocumentType::CPF) {
            $this->validateCpf($document);
        }
    }

    public function getFullName(): string
    {
        return trim("{$this->firstName} {$this->lastName}");
    }
}

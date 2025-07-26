<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\Enums\DocumentType;
use Flowcoders\Maestro\ValueObjects\Email;
use Flowcoders\Maestro\ValueObjects\PhoneNumber;
use Flowcoders\Maestro\ValueObjects\Cpf;
use Flowcoders\Maestro\ValueObjects\ValueObjectFactory;

readonly class CustomerDTO
{
    public function __construct(
        public ?string $id = null,
        public ?Email $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?Cpf $document = null,
        public ?DocumentType $documentType = null,
        public ?PhoneNumber $phone = null,
        public ?AddressDTO $address = null,
    ) {}

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
            email: ValueObjectFactory::createEmail($email),
            firstName: $firstName,
            lastName: $lastName,
            document: ValueObjectFactory::createCpf($document),
            documentType: $documentType,
            phone: ValueObjectFactory::createPhoneNumber($phone),
            address: $address,
        );
    }

    public function getFullName(): string
    {
        return trim("{$this->firstName} {$this->lastName}");
    }

    public function getEmailString(): ?string
    {
        return $this->email?->toString();
    }

    public function getPhoneString(): ?string
    {
        return $this->phone?->toString();
    }

    public function getDocumentString(): ?string
    {
        return $this->document?->toString();
    }
}

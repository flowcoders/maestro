<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use Flowcoders\Maestro\Enums\DocumentType;
use Flowcoders\Maestro\ValueObjects\Address;
use Flowcoders\Maestro\ValueObjects\Email;
use Flowcoders\Maestro\ValueObjects\PhoneNumber;
use Flowcoders\Maestro\ValueObjects\Cpf;
use InvalidArgumentException;

readonly class Customer
{
    public function __construct(
        public ?Email $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?Cpf $document = null,
        public ?DocumentType $documentType = null,
        public ?PhoneNumber $phone = null,
        public ?Address $address = null,
    ) {
        $this->validateRequiredFields();
        $this->validateDocumentConsistency();
    }

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
            email: $email ? new Email($email) : null,
            firstName: $firstName,
            lastName: $lastName,
            document: $document ? new Cpf($document) : null,
            documentType: $documentType,
            phone: $phone ? new PhoneNumber($phone) : null,
            address: $address,
        );
    }

    private function validateRequiredFields(): void
    {
        if (!$this->firstName && !$this->lastName && !$this->email) {
            throw new InvalidArgumentException('Customer must have at least firstName, lastName or email');
        }
    }

    private function validateDocumentConsistency(): void
    {
        if ($this->document && !$this->documentType) {
            throw new InvalidArgumentException('Document type is required when document is provided');
        }

        if ($this->documentType && !$this->document) {
            throw new InvalidArgumentException('Document is required when document type is provided');
        }
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

    public function hasValidDocument(): bool
    {
        return $this->document !== null && $this->documentType !== null;
    }

    public function toArray(): array
    {
        return [
            'email' => $this->getEmailString(),
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'full_name' => $this->getFullName(),
            'document' => $this->getDocumentString(),
            'document_type' => $this->documentType?->value,
            'phone' => $this->getPhoneString(),
            'address' => $this->address?->toArray(),
        ];
    }
}

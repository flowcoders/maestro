<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Factories;

use Flowcoders\Maestro\DTOs\CustomerDTO;
use Flowcoders\Maestro\ValueObjects\Customer;
use Flowcoders\Maestro\ValueObjects\Address;

class CustomerFactory
{
    /**
     * Converte CustomerDTO (interface externa) em Customer VO (com validação)
     */
    public static function fromDTO(CustomerDTO $customerDTO): Customer
    {
        return Customer::create(
            email: $customerDTO->email,
            firstName: $customerDTO->firstName,
            lastName: $customerDTO->lastName,
            document: $customerDTO->document,
            documentType: $customerDTO->documentType,
            phone: $customerDTO->phone,
            address: $customerDTO->address ? self::convertAddressDTO($customerDTO->address) : null,
        );
    }

    /**
     * Converte Customer VO de volta para CustomerDTO (para respostas)
     */
    public static function toDTO(Customer $customer, ?string $id = null): CustomerDTO
    {
        return CustomerDTO::create(
            id: $id,
            email: $customer->getEmailString(),
            firstName: $customer->firstName,
            lastName: $customer->lastName,
            document: $customer->getDocumentString(),
            documentType: $customer->documentType,
            phone: $customer->getPhoneString(),
            address: null, // TODO: implementar conversão Address → AddressDTO
        );
    }

    /**
     * Converte dados primitivos direto em Customer VO
     */
    public static function create(
        ?string $email = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $document = null,
        ?\Flowcoders\Maestro\Enums\DocumentType $documentType = null,
        ?string $phone = null,
        ?Address $address = null,
    ): Customer {
        return Customer::create(
            email: $email,
            firstName: $firstName,
            lastName: $lastName,
            document: $document,
            documentType: $documentType,
            phone: $phone,
            address: $address,
        );
    }

    private static function convertAddressDTO($addressDTO): ?Address
    {
        // TODO: Implementar conversão AddressDTO → Address VO
        // Por enquanto retorna null, implementaremos na próxima etapa
        return null;
    }
}

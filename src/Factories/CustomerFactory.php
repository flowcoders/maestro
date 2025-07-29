<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Factories;

use Flowcoders\Maestro\DTOs\CustomerDTO;
use Flowcoders\Maestro\ValueObjects\Customer;

class CustomerFactory
{
    public static function fromDTO(CustomerDTO $customerDTO): Customer
    {
        return Customer::create(
            email: $customerDTO->email,
            firstName: $customerDTO->firstName,
            lastName: $customerDTO->lastName,
            document: $customerDTO->document,
            documentType: $customerDTO->documentType,
            phone: $customerDTO->phone,
            address: $customerDTO->address ? AddressFactory::fromDTO($customerDTO->address) : null,
        );
    }

    public static function toDTO(Customer $customer, ?string $id = null): CustomerDTO
    {
        return new CustomerDTO(
            id: $id,
            email: $customer->getEmailString(),
            firstName: $customer->firstName,
            lastName: $customer->lastName,
            document: $customer->getDocumentString(),
            documentType: $customer->documentType,
            phone: $customer->getPhoneString(),
            address: $customer->address ? AddressFactory::toDTO($customer->address) : null,
        );
    }
}

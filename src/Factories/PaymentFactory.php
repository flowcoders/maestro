<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Factories;

use Flowcoders\Maestro\DTOs\CustomerDTO;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\DocumentType;
use Flowcoders\Maestro\Factories\PaymentMethodFactory;
use Flowcoders\Maestro\ValueObjects\Payment;

class PaymentFactory
{
    public static function createWithCreditCard(
        int $amount,
        Currency $currency,
        string $description,
        string $token,
        ?string $holderName = null,
        ?int $expirationMonth = null,
        ?int $expirationYear = null,
        ?string $brand = null,
        ?string $lastFourDigits = null,
        int $installments = 1,
        ?CustomerDTO $customer = null,
        ?string $externalReference = null,
        ?array $metadata = null,
        ?string $notificationUrl = null,
        ?string $callbackUrl = null,
    ): Payment {
        $paymentMethod = PaymentMethodFactory::createCreditCard(
            token: $token,
            holderName: $holderName,
            expirationMonth: $expirationMonth,
            expirationYear: $expirationYear,
            brand: $brand,
            lastFourDigits: $lastFourDigits,
        );

        return Payment::create(
            amount: $amount,
            currency: $currency,
            description: $description,
            paymentMethod: $paymentMethod,
            installments: $installments,
            customer: $customer,
            externalReference: $externalReference,
            metadata: $metadata,
            notificationUrl: $notificationUrl,
            callbackUrl: $callbackUrl,
        );
    }

    public static function createWithPix(
        int $amount,
        Currency $currency,
        string $description,
        int $expirationMinutes,
        CustomerDTO $customer, // PIX requires customer with document
        ?string $pixKey = null,
        ?string $externalReference = null,
        ?array $metadata = null,
        ?string $notificationUrl = null,
        ?string $callbackUrl = null,
    ): Payment {
        $paymentMethod = PaymentMethodFactory::createPix(
            expirationMinutes: $expirationMinutes,
            pixKey: $pixKey,
        );

        return Payment::create(
            amount: $amount,
            currency: $currency,
            description: $description,
            paymentMethod: $paymentMethod,
            installments: 1, // PIX doesn't support installments
            customer: $customer,
            externalReference: $externalReference,
            metadata: $metadata,
            notificationUrl: $notificationUrl,
            callbackUrl: $callbackUrl,
        );
    }

    public static function createWithDebitCard(
        int $amount,
        Currency $currency,
        string $description,
        string $token,
        ?string $holderName = null,
        ?int $expirationMonth = null,
        ?int $expirationYear = null,
        ?string $brand = null,
        ?string $lastFourDigits = null,
        ?CustomerDTO $customer = null,
        ?string $externalReference = null,
        ?array $metadata = null,
        ?string $notificationUrl = null,
        ?string $callbackUrl = null,
    ): Payment {
        $paymentMethod = PaymentMethodFactory::createDebitCard(
            token: $token,
            holderName: $holderName,
            expirationMonth: $expirationMonth,
            expirationYear: $expirationYear,
            brand: $brand,
            lastFourDigits: $lastFourDigits,
        );

        return Payment::create(
            amount: $amount,
            currency: $currency,
            description: $description,
            paymentMethod: $paymentMethod,
            installments: 1, // Debit cards don't support installments
            customer: $customer,
            externalReference: $externalReference,
            metadata: $metadata,
            notificationUrl: $notificationUrl,
            callbackUrl: $callbackUrl,
        );
    }

    public static function createWithBankSlip(
        int $amount,
        Currency $currency,
        string $description,
        int $expirationDays,
        CustomerDTO $customer, // Bank slip requires customer with document
        ?string $instructions = null,
        ?string $externalReference = null,
        ?array $metadata = null,
        ?string $notificationUrl = null,
        ?string $callbackUrl = null,
    ): Payment {
        $paymentMethod = PaymentMethodFactory::createBankSlip(
            expirationDays: $expirationDays,
            instructions: $instructions,
        );

        return Payment::create(
            amount: $amount,
            currency: $currency,
            description: $description,
            paymentMethod: $paymentMethod,
            installments: 1, // Bank slip doesn't support installments
            customer: $customer,
            externalReference: $externalReference,
            metadata: $metadata,
            notificationUrl: $notificationUrl,
            callbackUrl: $callbackUrl,
        );
    }

    /**
     * Create a PIX payment with customer data from strings
     */
    public static function createPixWithCustomerData(
        int $amount,
        Currency $currency,
        string $description,
        int $expirationMinutes,
        string $customerEmail,
        string $customerDocument,
        ?string $customerFirstName = null,
        ?string $customerLastName = null,
        ?string $customerPhone = null,
        ?string $pixKey = null,
        ?string $externalReference = null,
        ?array $metadata = null,
        ?string $notificationUrl = null,
        ?string $callbackUrl = null,
    ): Payment {
        $customer = CustomerDTO::create(
            email: $customerEmail,
            document: $customerDocument,
            documentType: DocumentType::CPF,
            firstName: $customerFirstName,
            lastName: $customerLastName,
            phone: $customerPhone,
        );

        return self::createWithPix(
            amount: $amount,
            currency: $currency,
            description: $description,
            expirationMinutes: $expirationMinutes,
            customer: $customer,
            pixKey: $pixKey,
            externalReference: $externalReference,
            metadata: $metadata,
            notificationUrl: $notificationUrl,
            callbackUrl: $callbackUrl,
        );
    }

    /**
     * Create a bank slip payment with customer data from strings
     */
    public static function createBankSlipWithCustomerData(
        int $amount,
        Currency $currency,
        string $description,
        int $expirationDays,
        string $customerEmail,
        string $customerDocument,
        ?string $customerFirstName = null,
        ?string $customerLastName = null,
        ?string $customerPhone = null,
        ?string $instructions = null,
        ?string $externalReference = null,
        ?array $metadata = null,
        ?string $notificationUrl = null,
        ?string $callbackUrl = null,
    ): Payment {
        $customer = CustomerDTO::create(
            email: $customerEmail,
            document: $customerDocument,
            documentType: DocumentType::CPF,
            firstName: $customerFirstName,
            lastName: $customerLastName,
            phone: $customerPhone,
        );

        return self::createWithBankSlip(
            amount: $amount,
            currency: $currency,
            description: $description,
            expirationDays: $expirationDays,
            customer: $customer,
            instructions: $instructions,
            externalReference: $externalReference,
            metadata: $metadata,
            notificationUrl: $notificationUrl,
            callbackUrl: $callbackUrl,
        );
    }
}

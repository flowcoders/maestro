<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Factories;

use Flowcoders\Maestro\DTOs\PaymentDTO;
use Flowcoders\Maestro\DTOs\PaymentMethods\PixDTO;
use Flowcoders\Maestro\DTOs\PaymentMethods\CreditCardDTO;
use Flowcoders\Maestro\Factories\PaymentMethodFactory;
use Flowcoders\Maestro\Factories\CustomerFactory;
use Flowcoders\Maestro\ValueObjects\Payment;

class PaymentFactory
{
    /**
     * Converte PaymentDTO (interface externa) em Payment VO (com validação)
     */
    public static function fromDTO(PaymentDTO $paymentDTO): Payment
    {
        // Converte PaymentMethod DTO → VO usando polimorfismo
        $paymentMethodVO = match($paymentDTO->paymentMethod->getType()) {
            'pix' => self::createPixFromDTO($paymentDTO->paymentMethod),
            'credit_card' => self::createCreditCardFromDTO($paymentDTO->paymentMethod),
            default => throw new \InvalidArgumentException("Unsupported payment method: {$paymentDTO->paymentMethod->getType()}")
        };

        // Converte Customer DTO → VO se existir
        $customerVO = $paymentDTO->customer 
            ? CustomerFactory::fromDTO($paymentDTO->customer)
            : null;

        return Payment::create(
            amount: $paymentDTO->amount,
            currency: $paymentDTO->currency,
            description: $paymentDTO->description,
            paymentMethod: $paymentMethodVO,
            installments: $paymentDTO->installments,
            customer: $customerVO,
            externalReference: $paymentDTO->externalReference,
            metadata: $paymentDTO->metadata,
            notificationUrl: $paymentDTO->notificationUrl,
            callbackUrl: $paymentDTO->callbackUrl,
        );
    }

    private static function createPixFromDTO(PixDTO $pixDTO): \Flowcoders\Maestro\ValueObjects\PaymentMethod\Pix
    {
        return PaymentMethodFactory::createPix($pixDTO->expiresAt);
    }

    private static function createCreditCardFromDTO(CreditCardDTO $cardDTO): \Flowcoders\Maestro\ValueObjects\PaymentMethod\CreditCard
    {
        return PaymentMethodFactory::createCreditCard(
            token: $cardDTO->token,
            holderName: $cardDTO->holderName,
            expirationMonth: $cardDTO->expirationMonth,
            expirationYear: $cardDTO->expirationYear,
            brand: $cardDTO->brand,
            lastFourDigits: $cardDTO->lastFourDigits,
        );
    }
}

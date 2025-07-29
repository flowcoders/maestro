<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Factories;

use Flowcoders\Maestro\DTOs\PaymentDTO;
use Flowcoders\Maestro\DTOs\PaymentMethods\CreditCardDTO;
use Flowcoders\Maestro\DTOs\PaymentMethods\PixDTO;
use Flowcoders\Maestro\ValueObjects\Payment;

class PaymentFactory
{
    public static function fromDTO(PaymentDTO $paymentDTO): Payment
    {
        $paymentMethodVO = match (true) {
            $paymentDTO->paymentMethod instanceof PixDTO => PaymentMethodFactory::createPixFromDTO($paymentDTO->paymentMethod),
            $paymentDTO->paymentMethod instanceof CreditCardDTO => PaymentMethodFactory::createCreditCardFromDTO($paymentDTO->paymentMethod),
            default => throw new \InvalidArgumentException('Unsupported payment method type: ' . get_class($paymentDTO->paymentMethod))
        };

        $customerVO = CustomerFactory::fromDTO($paymentDTO->customer);

        return new Payment(
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
}

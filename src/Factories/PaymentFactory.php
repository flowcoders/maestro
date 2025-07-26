<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Factories;

use Flowcoders\Maestro\DTOs\PaymentDTO;
use Flowcoders\Maestro\Enums\PaymentMethod;
use Flowcoders\Maestro\Factories\PaymentMethodFactory;
use Flowcoders\Maestro\Factories\CustomerFactory;
use Flowcoders\Maestro\ValueObjects\Payment;

class PaymentFactory
{
    public static function fromDTO(PaymentDTO $paymentDTO): Payment
    {
        $paymentMethodVO = match($paymentDTO->paymentMethod->getType()) {
            PaymentMethod::PIX->value => PaymentMethodFactory::createPixFromDTO($paymentDTO->paymentMethod),
            PaymentMethod::CREDIT_CARD->value => PaymentMethodFactory::createCreditCardFromDTO($paymentDTO->paymentMethod),
            default => throw new \InvalidArgumentException("Unsupported payment method: {$paymentDTO->paymentMethod->getType()}")
        };

        $customerVO = CustomerFactory::fromDTO($paymentDTO->customer);

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
}

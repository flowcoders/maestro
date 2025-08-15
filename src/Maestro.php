<?php

namespace Flowcoders\Maestro;

use Flowcoders\Maestro\Contracts\PaymentServiceProviderInterface;
use Flowcoders\Maestro\DTOs\PaymentRequest;
use Flowcoders\Maestro\DTOs\PaymentResponse;
use Illuminate\Support\Facades\App;

readonly class Maestro
{
    public function __construct(
        private PaymentServiceProviderInterface $paymentProvider
    ) {
    }

    public static function make(): self
    {
        return new self(
            App::make(PaymentServiceProviderInterface::class)
        );
    }

    public function createPayment(PaymentRequest $paymentRequest): PaymentResponse
    {
        return $this->paymentProvider->createPayment($paymentRequest);
    }
}

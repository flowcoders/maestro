<?php

namespace Flowcoders\Maestro;

use Flowcoders\Maestro\Contracts\PaymentServiceProviderInterface;
use Flowcoders\Maestro\DTOs\PaymentDTO;
use Flowcoders\Maestro\DTOs\PaymentResponseDTO;
use Flowcoders\Maestro\Factories\PaymentFactory;
use Illuminate\Support\Facades\App;

class Maestro
{
    public function __construct(
        private readonly PaymentServiceProviderInterface $paymentProvider
    ) {
    }

    public static function make(): self
    {
        return new self(
            App::make(PaymentServiceProviderInterface::class)
        );
    }

    public function createPayment(PaymentDTO $paymentDTO): PaymentResponseDTO
    {
        // Convert DTO to VO at the facade boundary
        $paymentVO = PaymentFactory::fromDTO($paymentDTO);
        
        // Pass validated VO to the service provider
        return $this->paymentProvider->createPayment($paymentVO);
    }
}

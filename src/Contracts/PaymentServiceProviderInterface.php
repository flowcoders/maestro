<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Contracts;

use Flowcoders\Maestro\DTOs\CreatePaymentDTO;
use Flowcoders\Maestro\DTOs\PaymentResponseDTO;
use Flowcoders\Maestro\DTOs\RefundPaymentDTO;

interface PaymentServiceProviderInterface
{
    public function createPayment(CreatePaymentDTO $paymentData): PaymentResponseDTO;

    public function getPayment(string $paymentId): PaymentResponseDTO;

    public function cancelPayment(string $paymentId): PaymentResponseDTO;

    public function refundPayment(RefundPaymentDTO $refundData): PaymentResponseDTO;
}

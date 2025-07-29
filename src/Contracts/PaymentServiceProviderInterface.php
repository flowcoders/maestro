<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Contracts;

use Flowcoders\Maestro\DTOs\PaymentResponseDTO;
use Flowcoders\Maestro\DTOs\RefundPaymentDTO;
use Flowcoders\Maestro\ValueObjects\Payment;

interface PaymentServiceProviderInterface
{
    public function createPayment(Payment $payment): PaymentResponseDTO;

    public function getPayment(string $paymentId): PaymentResponseDTO;

    public function cancelPayment(string $paymentId): PaymentResponseDTO;

    public function refundPayment(RefundPaymentDTO $refundData): PaymentResponseDTO;
}

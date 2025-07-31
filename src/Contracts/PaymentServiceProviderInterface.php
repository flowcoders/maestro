<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Contracts;

use Flowcoders\Maestro\DTOs\PaymentRequest;
use Flowcoders\Maestro\DTOs\PaymentResponse;
use Flowcoders\Maestro\DTOs\RefundRequest;

interface PaymentServiceProviderInterface
{
    public function createPayment(PaymentRequest $paymentRequest): PaymentResponse;

    public function getPayment(string $paymentId): PaymentResponse;

    public function cancelPayment(string $paymentId): PaymentResponse;

    public function refundPayment(RefundRequest $refundData): PaymentResponse;
}

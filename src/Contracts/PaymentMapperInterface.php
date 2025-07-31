<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Contracts;

use Flowcoders\Maestro\DTOs\PaymentResponse;
use Flowcoders\Maestro\DTOs\RefundRequest;
use Flowcoders\Maestro\DTOs\PaymentRequest;

interface PaymentMapperInterface
{
    public function mapCreatePaymentRequest(PaymentRequest $paymentRequest): array;
    public function mapRefundPaymentRequest(RefundRequest $refundRequest): array;
    public function mapPaymentResponse(array $response): PaymentResponse;
}

<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Contracts;

use Flowcoders\Maestro\DTOs\PaymentRequest;
use Flowcoders\Maestro\DTOs\PaymentResponse;
use Flowcoders\Maestro\DTOs\RefundRequest;
use Flowcoders\Maestro\DTOs\RefundResponse;

interface PaymentMapperInterface
{
    public function mapPaymentRequest(PaymentRequest $paymentRequest): array;
    public function mapPaymentResponse(array $response): PaymentResponse;
    public function mapRefundPaymentRequest(RefundRequest $refundRequest): array;
    public function mapRefundResponse(array $response): RefundResponse;
}

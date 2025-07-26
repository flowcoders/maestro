<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Contracts;

use Flowcoders\Maestro\ValueObjects\Payment;
use Flowcoders\Maestro\DTOs\PaymentResponseDTO;
use Flowcoders\Maestro\DTOs\RefundPaymentDTO;

interface PaymentMapperInterface
{
    /**
     * Mapeia Payment VO (validado) para formato específico do PSP
     */
    public function mapCreatePaymentRequest(Payment $payment): array;

    /**
     * Mapeia refund request (ainda usando DTO pois é específico)
     */
    public function mapRefundPaymentRequest(RefundPaymentDTO $dto): array;

    /**
     * Mapeia resposta do PSP para PaymentResponseDTO (interface externa)
     */
    public function mapPaymentResponse(array $response): PaymentResponseDTO;
}

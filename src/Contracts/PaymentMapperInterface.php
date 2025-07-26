<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Contracts;

use Flowcoders\Maestro\DTOs\CreatePaymentDTO;
use Flowcoders\Maestro\DTOs\PaymentResponseDTO;
use Flowcoders\Maestro\DTOs\RefundPaymentDTO;

interface PaymentMapperInterface
{
    public function mapCreatePaymentRequest(CreatePaymentDTO $dto): array;

    public function mapRefundPaymentRequest(RefundPaymentDTO $dto): array;

    public function mapPaymentResponse(array $response): PaymentResponseDTO;
}

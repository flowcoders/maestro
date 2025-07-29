<?php

namespace Flowcoders\Maestro\Facades;

use Flowcoders\Maestro\Contracts\PaymentServiceProviderInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Flowcoders\Maestro\Contracts\PaymentServiceProviderInterface
 *
 * @method static \Flowcoders\Maestro\DTOs\PaymentResponseDTO createPayment(\Flowcoders\Maestro\ValueObjects\Payment $payment)
 * @method static \Flowcoders\Maestro\DTOs\PaymentResponseDTO getPayment(string $paymentId)
 * @method static \Flowcoders\Maestro\DTOs\PaymentResponseDTO cancelPayment(string $paymentId)
 * @method static \Flowcoders\Maestro\DTOs\PaymentResponseDTO refundPayment(\Flowcoders\Maestro\DTOs\RefundPaymentDTO $refundData)
 */
class Maestro extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PaymentServiceProviderInterface::class;
    }
}

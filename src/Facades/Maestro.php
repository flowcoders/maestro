<?php

namespace Flowcoders\Maestro\Facades;

use Flowcoders\Maestro\Contracts\PaymentServiceProviderInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Flowcoders\Maestro\Contracts\PaymentServiceProviderInterface
 *
 * @method static \Flowcoders\Maestro\DTOs\PaymentResponse createPayment(\Flowcoders\Maestro\DTOs\PaymentRequest $paymentRequest)
 * @method static \Flowcoders\Maestro\DTOs\PaymentResponse getPayment(string $paymentId)
 * @method static \Flowcoders\Maestro\DTOs\PaymentResponse cancelPayment(string $paymentId)
 * @method static \Flowcoders\Maestro\DTOs\PaymentResponse refundPayment(\Flowcoders\Maestro\DTOs\RefundRequest $refundRequest)
 */
class Maestro extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PaymentServiceProviderInterface::class;
    }
}

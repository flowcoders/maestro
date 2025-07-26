<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Adapters;

use Flowcoders\Maestro\Contracts\HttpClientInterface;
use Flowcoders\Maestro\Contracts\PaymentMapperInterface;
use Flowcoders\Maestro\Contracts\PaymentServiceProviderInterface;
use Flowcoders\Maestro\DTOs\CreatePaymentDTO;
use Flowcoders\Maestro\DTOs\PaymentResponseDTO;
use Flowcoders\Maestro\DTOs\RefundPaymentDTO;
use Flowcoders\Maestro\Exceptions\PaymentException;

class MercadoPagoAdapter implements PaymentServiceProviderInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly PaymentMapperInterface $mapper,
    ) {
    }

    public function createPayment(CreatePaymentDTO $paymentData): PaymentResponseDTO
    {
        try {
            $requestData = $this->mapper->mapCreatePaymentRequest($paymentData);

            $response = $this->httpClient->post('/v1/payments', $requestData);

            if (!$response->isSuccessful()) {
                throw new PaymentException(
                    "Failed to create payment: {$response->error}",
                    $response->statusCode
                );
            }

            return $this->mapper->mapPaymentResponse($response->data);
        } catch (PaymentException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new PaymentException(
                "Unexpected error creating payment: {$exception->getMessage()}",
                0,
                $exception
            );
        }
    }

    public function getPayment(string $paymentId): PaymentResponseDTO
    {
        try {
            $response = $this->httpClient->get("/v1/payments/{$paymentId}");

            if (!$response->isSuccessful()) {
                throw new PaymentException(
                    "Failed to get payment: {$response->error}",
                    $response->statusCode
                );
            }

            return $this->mapper->mapPaymentResponse($response->data);
        } catch (PaymentException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new PaymentException(
                "Unexpected error getting payment: {$exception->getMessage()}",
                0,
                $exception
            );
        }
    }

    public function cancelPayment(string $paymentId): PaymentResponseDTO
    {
        try {
            $response = $this->httpClient->put("/v1/payments/{$paymentId}", [
                'status' => 'cancelled',
            ]);

            if (!$response->isSuccessful()) {
                throw new PaymentException(
                    "Failed to cancel payment: {$response->error}",
                    $response->statusCode
                );
            }

            return $this->mapper->mapPaymentResponse($response->data);
        } catch (PaymentException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new PaymentException(
                "Unexpected error cancelling payment: {$exception->getMessage()}",
                0,
                $exception
            );
        }
    }

    public function refundPayment(RefundPaymentDTO $refundData): PaymentResponseDTO
    {
        try {
            $requestData = $this->mapper->mapRefundPaymentRequest($refundData);

            $response = $this->httpClient->post("/v1/payments/{$refundData->paymentId}/refunds", $requestData);

            if (!$response->isSuccessful()) {
                throw new PaymentException(
                    "Failed to refund payment: {$response->error}",
                    $response->statusCode
                );
            }

            // After refund, get the updated payment status
            return $this->getPayment($refundData->paymentId);
        } catch (PaymentException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new PaymentException(
                "Unexpected error refunding payment: {$exception->getMessage()}",
                0,
                $exception
            );
        }
    }
}

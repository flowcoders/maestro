<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Adapters;

use Flowcoders\Maestro\Contracts\HttpClientInterface;
use Flowcoders\Maestro\Contracts\PaymentMapperInterface;
use Flowcoders\Maestro\Contracts\PaymentServiceProviderInterface;
use Flowcoders\Maestro\DTOs\PaymentResponse;
use Flowcoders\Maestro\DTOs\RefundRequest;
use Flowcoders\Maestro\DTOs\PaymentRequest;
use Flowcoders\Maestro\Exceptions\PaymentException;

readonly class MercadoPagoAdapter implements PaymentServiceProviderInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private PaymentMapperInterface $mapper,
    ) {
    }

    public function createPayment(PaymentRequest $paymentRequest): PaymentResponse
    {
        try {
            $requestData = $this->mapper->mapPaymentRequest($paymentRequest);

            $response = $this->httpClient->post('/v1/payments', $requestData);

            if (! $response->isSuccessful()) {
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

    public function getPayment(string $paymentId): PaymentResponse
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

    public function cancelPayment(string $paymentId): PaymentResponse
    {
        try {
            $response = $this->httpClient->put("/v1/payments/{$paymentId}", [
                'status' => 'canceled',
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

    public function refundPayment(RefundRequest $refundRequest): PaymentResponse
    {
        try {
            $requestData = $this->mapper->mapRefundPaymentRequest($refundRequest);

            $response = $this->httpClient->post("/v1/payments/{$refundRequest->paymentId}/refunds", $requestData);

            if (!$response->isSuccessful()) {
                throw new PaymentException(
                    "Failed to refund payment: {$response->error}",
                    $response->statusCode
                );
            }

            // After refund, get the updated payment status
            return $this->getPayment($refundRequest->paymentId);
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

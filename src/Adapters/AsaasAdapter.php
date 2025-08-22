<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Adapters;

use Flowcoders\Maestro\Contracts\HttpClientInterface;
use Flowcoders\Maestro\Contracts\PaymentMapperInterface;
use Flowcoders\Maestro\Contracts\PaymentServiceProviderInterface;
use Flowcoders\Maestro\DTOs\Customer;
use Flowcoders\Maestro\DTOs\PaymentResponse;
use Flowcoders\Maestro\DTOs\RefundRequest;
use Flowcoders\Maestro\DTOs\PaymentRequest;
use Flowcoders\Maestro\DTOs\RefundResponse;
use Flowcoders\Maestro\Exceptions\PaymentException;

readonly class AsaasAdapter implements PaymentServiceProviderInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private PaymentMapperInterface $mapper,
    ) {
    }

    public function createPayment(PaymentRequest $paymentRequest): PaymentResponse
    {
        try {
            if ($paymentRequest->customer->id === null) {
                $customerId = $this->firstOrCreateCustomer($paymentRequest->customer);

                $customer = new \Flowcoders\Maestro\DTOs\Customer(
                    id: $customerId,
                    firstName: $paymentRequest->customer->firstName,
                    lastName: $paymentRequest->customer->lastName,
                    email: $paymentRequest->customer->email,
                    document: $paymentRequest->customer->document,
                    phone: $paymentRequest->customer->phone,
                    address: $paymentRequest->customer->address,
                );

                $paymentRequest = new \Flowcoders\Maestro\DTOs\PaymentRequest(
                    money: $paymentRequest->money,
                    customer: $customer,
                    paymentMethod: $paymentRequest->paymentMethod,
                    description: $paymentRequest->description,
                    externalReference: $paymentRequest->externalReference,
                    installments: $paymentRequest->installments,
                    capture: $paymentRequest->capture,
                    statementDescriptor: $paymentRequest->statementDescriptor,
                    notificationUrl: $paymentRequest->notificationUrl,
                    metadata: $paymentRequest->metadata,
                    idempotencyKey: $paymentRequest->idempotencyKey,
                    token: $paymentRequest->token,
                );
            }

            $requestData = $this->mapper->mapPaymentRequest($paymentRequest);

            $headers = [];
            if ($paymentRequest->idempotencyKey !== null) {
                $headers['Asaas-Idempotency-Key'] = $paymentRequest->idempotencyKey;
            }

            $response = $this->httpClient->post('/payments', $requestData, $headers);

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
            $response = $this->httpClient->get("/payments/{$paymentId}");

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
            $response = $this->httpClient->delete("/payments/{$paymentId}");

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

    public function refundPayment(RefundRequest $refundRequest): RefundResponse
    {
        try {
            $requestData = $this->mapper->mapRefundPaymentRequest($refundRequest);

            $response = $this->httpClient->post("/payments/{$refundRequest->paymentId}/refund", $requestData);

            if (!$response->isSuccessful()) {
                throw new PaymentException(
                    "Failed to refund payment: {$response->error}",
                    $response->statusCode
                );
            }

            return $this->mapper->mapRefundResponse($response->data);
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

    private function firstOrCreateCustomer(Customer $customer): string
    {
        if ($customer->document !== null) {
            $existingCustomerId = $this->findCustomerByDocument($customer->document->value);
            if ($existingCustomerId !== null) {
                return $existingCustomerId;
            }
        }

        return $this->createCustomer($customer);
    }

    private function createCustomer(Customer $customer): string
    {
        $customerData = $this->prepareCustomerData($customer);

        if (empty($customerData['name'])) {
            throw new PaymentException('Customer name is required for Asaas');
        }

        if (!isset($customerData['email']) && !isset($customerData['phone'])) {
            throw new PaymentException('Either email or phone is required for Asaas customer creation');
        }

        $response = $this->httpClient->post('/customers', $customerData);

        if (!$response->isSuccessful()) {
            throw new PaymentException(
                "Failed to create customer: {$response->error}",
                $response->statusCode
            );
        }

        return $response->data['id'] ?? throw new PaymentException('Customer ID not returned from Asaas');
    }

    private function prepareCustomerData(Customer $customer): array
    {
        $customerData = [];

        $customerData['name'] = trim(($customer->firstName ?? '') . ' ' . ($customer->lastName ?? ''));

        if ($customer->email !== null) {
            $customerData['email'] = $customer->email->value;
        }

        if ($customer->phone !== null) {
            $customerData['phone'] = $customer->phone->number;
        }

        if ($customer->document !== null) {
            $customerData['cpfCnpj'] = $customer->document->value;
        }

        if ($customer->address !== null) {
            if (!empty($customer->address->postalCode)) {
                $customerData['postalCode'] = $customer->address->postalCode;
            }
            if ($customer->address->streetLine2 !== null) {
                $customerData['addressNumber'] = $customer->address->streetLine2;
            }
            $customerData['city'] = $customer->address->city;
            if ($customer->address->neighborhood !== null) {
                $customerData['province'] = $customer->address->neighborhood;
            }
        }

        return $customerData;
    }

    private function findCustomerByDocument(string $cpfCnpj): ?string
    {
        $response = $this->httpClient->get('/customers', ['cpfCnpj' => $cpfCnpj]);

        if (!$response->isSuccessful()) {
            return null;
        }

        if (isset($response->data['data']) && is_array($response->data['data']) && count($response->data['data']) > 0) {
            return $response->data['data'][0]['id'] ?? null;
        }

        return null;
    }
}

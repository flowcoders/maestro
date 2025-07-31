<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Mappers;

use Flowcoders\Maestro\Contracts\PaymentMapperInterface;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\PaymentMethod;
use Flowcoders\Maestro\Enums\PaymentStatus;
use DateTimeImmutable;
use Flowcoders\Maestro\Contracts\PaymentMethodInterface;
use Flowcoders\Maestro\DTOs\Customer;
use Flowcoders\Maestro\DTOs\PaymentRequest;
use Flowcoders\Maestro\DTOs\PaymentResponse;
use Flowcoders\Maestro\DTOs\RefundRequest;
use Flowcoders\Maestro\ValueObjects\Address;

class MercadoPagoPaymentMapper implements PaymentMapperInterface
{
    public function mapCreatePaymentRequest(PaymentRequest $paymentRequest): array
    {
        $data = [
            'transaction_amount' => $paymentRequest->money->amount / 100, // Convert cents to decimal
            'description' => $paymentRequest->description,
            'installments' => $paymentRequest->installments,
            'payment_method_id' => $this->mapPaymentMethod($paymentRequest->paymentMethod), // TODO: MercadoPago uses some brands as payment_method_id
            'external_reference' => $paymentRequest->externalReference,
            'notification_url' => $paymentRequest->notificationUrl,
            'callback_url' => $paymentRequest->callbackUrl,
        ];

        $data['payer'] = $this->mapCustomer($paymentRequest->customer);

        if ($paymentRequest->metadata !== null) {
            $data['metadata'] = $paymentRequest->metadata;
        }

        return array_filter($data, fn ($value) => $value !== null);
    }

    public function mapRefundPaymentRequest(RefundRequest $refundRequest): array
    {
        $data = [
            'amount' => $refundRequest->amount !== null ? $refundRequest->amount / 100 : null, // Convert cents to decimal
            'reason' => $refundRequest->reason,
        ];

        if ($refundRequest->metadata !== null) {
            $data['metadata'] = $refundRequest->metadata;
        }

        return array_filter($data, fn ($value) => $value !== null);
    }

    public function mapPaymentResponse(array $response): PaymentResponse
    {
        $customer = null;
        if (isset($response['payer'])) {
            $customer = $this->mapCustomerFromResponse($response['payer']);
        }

        return new PaymentResponse(
            id: (string) $response['id'],
            status: $this->mapStatus($response['status']),
            amount: (int) ($response['transaction_amount'] * 100), // Convert to cents
            currency: Currency::from($response['currency_id']),
            description: $response['description'] ?? null,
            customer: $customer,
            externalReference: $response['external_reference'] ?? null,
            paymentMethod: $response['payment_method_id'] ?? null,
            createdAt: isset($response['date_created'])
                ? new DateTimeImmutable($response['date_created'])
                : null,
            updatedAt: isset($response['date_last_updated'])
                ? new DateTimeImmutable($response['date_last_updated'])
                : null,
            metadata: $response['metadata'] ?? null,
            pspResponse: $response,
            error: $response['status_detail'] ?? null,
            errorCode: isset($response['status']) && $response['status'] === 'rejected'
                ? $response['status_detail']
                : null,
        );
    }

    private function mapCustomer(Customer $customer): array
    {
        $data = [
            'id' => $customer->id,
            'email' => $customer->email->value,
            'first_name' => $customer->firstName,
            'last_name' => $customer->lastName,
            'phone' => [
                'number' => $customer->phone->number,
            ],
            'identification' => [
                'type' => $customer->document->type->value,
                'number' => $customer->document->value,
            ],
        ];

        if ($customer->address !== null) {
            $data['address'] = $this->mapAddress($customer->address);
        }

        return array_filter($data, fn ($value) => $value !== null);
    }

    private function mapAddress(Address $address): array
    {
        return array_filter([
            'zip_code' => $address->postalCode,
            'street_name' => $address->streetLine1,
            'street_number' => $address->streetLine2,
            'city' => $address->city,
            'federal_unit' => $address->stateOrProvince,
            'neighborhood' => $address->neighborhood,
        ], fn ($value) => $value !== null);
    }

    /**
     * Mapeia PaymentMethod VO para string do MercadoPago
     */
    private function mapPaymentMethod(PaymentMethodInterface $paymentMethod): string
    {
        return match($paymentMethod->getType()) {
            PaymentMethod::PIX->value => 'pix',
            PaymentMethod::CREDIT_CARD->value => 'credit_card',
            default => throw new \InvalidArgumentException("Unsupported payment method: {$paymentMethod->getType()}")
        };
    }

    private function mapStatus(string $status): PaymentStatus
    {
        return match ($status) {
            'pending' => PaymentStatus::PENDING,
            'approved' => PaymentStatus::APPROVED,
            'authorized' => PaymentStatus::AUTHORIZED,
            'in_process' => PaymentStatus::IN_PROCESS,
            'in_mediation' => PaymentStatus::IN_MEDIATION,
            'rejected' => PaymentStatus::REJECTED,
            'canceled' => PaymentStatus::CANCELED,
            'refunded' => PaymentStatus::REFUNDED,
            'charged_back' => PaymentStatus::CHARGED_BACK,
            default => PaymentStatus::PENDING,
        };
    }

    private function mapCustomerFromResponse(array $customer): Customer
    {
        return new Customer(
            email: $customer['email'],
            firstName: $customer['first_name'],
            lastName: $customer['last_name'],
        );
    }
}

<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Mappers;

use Flowcoders\Maestro\Contracts\PaymentMapperInterface;
use Flowcoders\Maestro\DTOs\AddressDTO;
use Flowcoders\Maestro\DTOs\CreatePaymentDTO;
use Flowcoders\Maestro\DTOs\CustomerDTO;
use Flowcoders\Maestro\DTOs\PaymentResponseDTO;
use Flowcoders\Maestro\DTOs\RefundPaymentDTO;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\PaymentStatus;
use DateTimeImmutable;

class MercadoPagoPaymentMapper implements PaymentMapperInterface
{
    public function mapCreatePaymentRequest(CreatePaymentDTO $dto): array
    {
        $data = [
            'transaction_amount' => $dto->amount / 100, // Convert cents to decimal
            'description' => $dto->description,
            'installments' => $dto->installments,
            'payment_method_id' => $dto->paymentMethod,
            'external_reference' => $dto->externalReference,
            'notification_url' => $dto->notificationUrl,
            'callback_url' => $dto->callbackUrl,
        ];

        if ($dto->customer !== null) {
            $data['payer'] = $this->mapCustomer($dto->customer);
        }

        if ($dto->metadata !== null) {
            $data['metadata'] = $dto->metadata;
        }

        return array_filter($data, fn ($value) => $value !== null);
    }

    public function mapRefundPaymentRequest(RefundPaymentDTO $dto): array
    {
        $data = [
            'amount' => $dto->amount !== null ? $dto->amount / 100 : null, // Convert cents to decimal
            'reason' => $dto->reason,
        ];

        if ($dto->metadata !== null) {
            $data['metadata'] = $dto->metadata;
        }

        return array_filter($data, fn ($value) => $value !== null);
    }

    public function mapPaymentResponse(array $response): PaymentResponseDTO
    {
        $customer = null;
        if (isset($response['payer'])) {
            $customer = $this->mapCustomerFromResponse($response['payer']);
        }

        return new PaymentResponseDTO(
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

    private function mapCustomer(CustomerDTO $customer): array
    {
        $data = [
            'id' => $customer->id,
            'email' => $customer->email,
            'first_name' => $customer->firstName,
            'last_name' => $customer->lastName,
            'phone' => [
                'number' => $customer->phone,
            ],
            'identification' => [
                'type' => $customer->documentType,
                'number' => $customer->document,
            ],
        ];

        if ($customer->address !== null) {
            $data['address'] = $this->mapAddress($customer->address);
        }

        return array_filter($data, fn ($value) => $value !== null);
    }

    private function mapAddress(AddressDTO $address): array
    {
        return array_filter([
            'street_name' => $address->streetName,
            'street_number' => $address->streetNumber,
            'zip_code' => $address->postalCode,
            'city' => $address->city,
            'federal_unit' => $address->state,
            'neighborhood' => $address->neighborhood,
        ], fn ($value) => $value !== null);
    }

    private function mapCustomerFromResponse(array $payer): CustomerDTO
    {
        $address = null;
        if (isset($payer['address'])) {
            $address = new AddressDTO(
                streetName: $payer['address']['street_name'] ?? null,
                streetNumber: $payer['address']['street_number'] ?? null,
                postalCode: $payer['address']['zip_code'] ?? null,
                city: $payer['address']['city'] ?? null,
                state: $payer['address']['federal_unit'] ?? null,
                neighborhood: $payer['address']['neighborhood'] ?? null,
            );
        }

        return new CustomerDTO(
            id: $payer['id'] ?? null,
            email: $payer['email'] ?? null,
            firstName: $payer['first_name'] ?? null,
            lastName: $payer['last_name'] ?? null,
            document: $payer['identification']['number'] ?? null,
            documentType: $payer['identification']['type'] ?? null,
            phone: $payer['phone']['number'] ?? null,
            address: $address,
        );
    }

    private function mapStatus(string $status): PaymentStatus
    {
        return match ($status) {
            'pending' => PaymentStatus::Pending,
            'approved' => PaymentStatus::Approved,
            'authorized' => PaymentStatus::Authorized,
            'in_process' => PaymentStatus::InProcess,
            'in_mediation' => PaymentStatus::InMediation,
            'rejected' => PaymentStatus::Rejected,
            'cancelled' => PaymentStatus::Cancelled,
            'refunded' => PaymentStatus::Refunded,
            'charged_back' => PaymentStatus::ChargedBack,
            default => PaymentStatus::Pending,
        };
    }
}

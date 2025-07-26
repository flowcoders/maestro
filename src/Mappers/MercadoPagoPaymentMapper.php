<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Mappers;

use Flowcoders\Maestro\Contracts\PaymentMapperInterface;
use Flowcoders\Maestro\DTOs\PaymentResponseDTO;
use Flowcoders\Maestro\DTOs\RefundPaymentDTO;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\PaymentStatus;
use DateTimeImmutable;
use Flowcoders\Maestro\Contracts\ValueObjects\PaymentMethodInterface;
use Flowcoders\Maestro\Enums\PaymentMethod;
use Flowcoders\Maestro\ValueObjects\Address;
use Flowcoders\Maestro\ValueObjects\Customer;
use Flowcoders\Maestro\ValueObjects\Payment;

class MercadoPagoPaymentMapper implements PaymentMapperInterface
{
    public function mapCreatePaymentRequest(Payment $payment): array
    {
        $data = [
            'transaction_amount' => $payment->amount / 100, // Convert cents to decimal
            'description' => $payment->description,
            'installments' => $payment->installments,
            'payment_method_id' => $this->mapPaymentMethod($payment->paymentMethod), // TODO: MercadoPago uses some brands as payment_method_id
            'external_reference' => $payment->externalReference,
            'notification_url' => $payment->notificationUrl,
            'callback_url' => $payment->callbackUrl,
        ];

        $data['payer'] = $this->mapCustomer($payment->customer);

        if ($payment->metadata !== null) {
            $data['metadata'] = $payment->metadata;
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

    private function mapCustomer(Customer $customer): array
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

    private function mapAddress(Address $address): array
    {
        return array_filter([
            'zip_code' => $address->postalCode,
            'street_name' => $address->streetName,
            'street_number' => $address->streetNumber,
            'city' => $address->city,
            'federal_unit' => $address->state,
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
}

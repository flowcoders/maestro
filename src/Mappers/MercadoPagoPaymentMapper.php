<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Mappers;

use Carbon\CarbonImmutable;
use Flowcoders\Maestro\Contracts\PaymentMapperInterface;
use Flowcoders\Maestro\DTOs\RefundResponse;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\DocumentType;
use Flowcoders\Maestro\Enums\PaymentMethod;
use Flowcoders\Maestro\Enums\PaymentStatus;
use Flowcoders\Maestro\Contracts\PaymentMethodInterface;
use Flowcoders\Maestro\DTOs\Customer;
use Flowcoders\Maestro\DTOs\PaymentRequest;
use Flowcoders\Maestro\DTOs\PaymentResponse;
use Flowcoders\Maestro\DTOs\RefundRequest;
use Flowcoders\Maestro\Enums\CardBrand;
use Flowcoders\Maestro\Enums\RefundStatus;
use Flowcoders\Maestro\ValueObjects\Address;
use Flowcoders\Maestro\ValueObjects\Document;
use Flowcoders\Maestro\ValueObjects\Money;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\CreditCard;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\Pix;

class MercadoPagoPaymentMapper implements PaymentMapperInterface
{
    public function mapPaymentRequest(PaymentRequest $paymentRequest): array
    {
        $data = [
            'capture' => $paymentRequest->capture,
            'description' => $paymentRequest->description,
            'transaction_amount' => $paymentRequest->money->amount / 100,
            'external_reference' => $paymentRequest->externalReference,
            'installments' => $paymentRequest->installments,
            'notification_url' => $paymentRequest->notificationUrl,
            'token' => $paymentRequest->token,
        ];

        $data['payer'] = $this->mapCustomer($paymentRequest->customer);
        $data['payment_method_id'] = $this->mapPaymentMethod($paymentRequest->paymentMethod);

        if ($paymentRequest->paymentMethod instanceof Pix) {
            $data['date_of_expiration'] = $paymentRequest->paymentMethod->getExpiresAt()->format('Y-m-d\TH:i:s.vP');
        }

        if ($paymentRequest->metadata !== null) {
            $data['metadata'] = $paymentRequest->metadata;
        }

        return array_filter($data, fn ($value) => $value !== null);
    }

    public function mapRefundPaymentRequest(RefundRequest $refundRequest): array
    {
        $data = [
            'id' => $refundRequest->paymentId,
        ];

        if ($refundRequest->money !== null) {
            $data['amount'] = $refundRequest->money->amount / 100;
        }

        return array_filter($data, fn ($value) => $value !== null);
    }

    public function mapPaymentResponse(array $response): PaymentResponse
    {
        $customer = null;
        if (isset($response['payer'])) {
            $customer = $this->mapCustomerFromResponse($response['payer']);
        }

        $money = new Money(
            amount: (int) ($response['transaction_amount'] * 100),
            currency: Currency::from($response['currency_id']),
        );

        $paymentMethod = null;
        if (isset($response['payment_method_id'])) {
            $paymentMethod = $this->mapPaymentMethodFromResponse($response);
        }

        return new PaymentResponse(
            id: (string) $response['id'],
            status: $this->mapStatusFromResponse($response['status']),
            money: $money,
            description: $response['description'] ?? null,
            customer: $customer,
            externalReference: $response['external_reference'] ?? null,
            paymentMethod: $paymentMethod,
            capture: $response['captured'] ?? null,
            statementDescriptor: $response['statement_descriptor'] ?? null,
            installments: $response['installments'] ?? null,
            notificationUrl: $response['notification_url'] ?? null,
            metadata: $response['metadata'] ?? null,
            pspResponse: $response,
            error: $response['status_detail'] ?? null,
            errorCode: isset($response['status']) && $response['status'] === 'rejected'
                ? $response['status_detail']
                : null,
            createdAt: isset($response['date_created'])
                ? new CarbonImmutable($response['date_created'])
                : null,
            updatedAt: isset($response['date_last_updated'])
                ? new CarbonImmutable($response['date_last_updated'])
                : null,
        );
    }

    public function mapRefundResponse(array $response): RefundResponse
    {
        return new RefundResponse(
            id: (string) $response['id'],
            paymentId: (string) $response['payment_id'],
            amount:(int) ($response['amount'] * 100),
            status: $this->mapStatusFromRefundResponse($response['status']),
            reason: $response['reason'] ?? null,
            metadata: $response['metadata'] ?? null,
            pspResponse: $response,
            error: $response['status_detail'] ?? null,
            errorCode: isset($response['status']) && $response['status'] === 'rejected'
                ? $response['status_detail']
                : null,
            createdAt: isset($response['date_created'])
                ? new CarbonImmutable($response['date_created'])
                : null,
        );
    }

    private function mapCustomer(Customer $customer): array
    {
        $data = [
            'type' => 'customer',
            'email' => $customer->email->value,
            'first_name' => $customer->firstName,
            'last_name' => $customer->lastName,
            'identification' => [
                'type' => $customer->document->type->value,
                'number' => $customer->document->value,
            ],
        ];

        if ($customer->id !== null) {
            $data['id'] = $customer->id;
        }

        if ($customer->phone !== null) {
            $data['phone'] = $customer->phone->number;
        }

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
        ], fn ($value) => $value !== null);
    }

    private function mapPaymentMethod(PaymentMethodInterface $paymentMethod): string
    {
        if ($paymentMethod instanceof Pix) {
            return 'pix';
        }

        if ($paymentMethod instanceof CreditCard) {
            return $this->mapBrand($paymentMethod->brand);
        }

        return match($paymentMethod->getType()) {
            PaymentMethod::PIX->value => 'pix',
            PaymentMethod::CREDIT_CARD->value => 'credit_card',
            default => throw new \InvalidArgumentException("Unsupported payment method: {$paymentMethod->getType()}")
        };
    }

    private function mapBrand(CardBrand $brand): string
    {
        return match($brand) {
            CardBrand::AMEX => 'amex',
            CardBrand::ELO => 'elo',
            CardBrand::HIPERCARD => 'hipercard',
            CardBrand::MASTER => 'master',
            CardBrand::VISA => 'visa',

            default => throw new \InvalidArgumentException("Unsupported card brand: {$brand->value}")
        };
    }

    private function mapStatusFromResponse(string $status): PaymentStatus
    {
        return match ($status) {
            'approved' => PaymentStatus::APPROVED,
            'authorized' => PaymentStatus::AUTHORIZED,
            'in_process' => PaymentStatus::IN_PROCESS,
            'in_dispute' => PaymentStatus::IN_DISPUTE,
            'rejected' => PaymentStatus::REFUSED,
            'canceled' => PaymentStatus::CANCELED,
            'refunded' => PaymentStatus::REFUNDED,
            'charged_back' => PaymentStatus::CHARGED_BACK,
            default => PaymentStatus::PENDING,
        };
    }

    private function mapStatusFromRefundResponse(string $status): RefundStatus
    {
        return match ($status) {
            'approved' => RefundStatus::APPROVED,
            'in_process' => RefundStatus::IN_PROCESS,
            'rejected' => RefundStatus::REJECTED,
            'canceled' => RefundStatus::CANCELED,
            default => RefundStatus::PENDING,
        };
    }

    private function mapPaymentMethodFromResponse(array $response): ?PaymentMethodInterface
    {
        $paymentTypeId = $response['payment_type_id'] ?? null;

        if ($paymentTypeId === null) {
            return null;
        }

        if ($paymentTypeId === 'bank_transfer' && $response['payment_method_id'] === 'pix') {
            $expiresAt = $this->calculatePixExpirationInMinutes($response['date_of_expiration'] ?? null);
            $qrCode = data_get($response, 'point_of_interaction.transaction_data.qr_code');
            $qrCodeBase64 = data_get($response, 'point_of_interaction.transaction_data.qr_code_base64');
            $qrCodeUrl = data_get($response, 'point_of_interaction.transaction_data.ticket_url');

            return new Pix(
                expiresAt: $expiresAt,
                qrCode: $qrCode,
                qrCodeBase64: $qrCodeBase64,
                qrCodeUrl: $qrCodeUrl
            );
        }

        if (in_array($paymentTypeId, ['credit_card', 'debit_card', 'prepaid_card'])) {
            $paymentMethodId = $response['payment_method_id'] ?? null;
            $cardBrand = $paymentMethodId ? $this->mapBrandFromResponse($paymentMethodId) : null;

            return new CreditCard(
                holderName: $response['card']['cardholder']['name'] ?? null,
                expiryMonth: $response['card']['expiration_month'] ?? null,
                expiryYear: $response['card']['expiration_year'] ?? null,
                brand: $cardBrand,
                lastFourDigits: $response['card']['last_four_digits'] ?? null,
            );
        }

        return null;
    }

    private function calculatePixExpirationInMinutes(?string $dateOfExpiration): int
    {
        if ($dateOfExpiration === null) {
            throw new \InvalidArgumentException('Date of expiration is required for PIX payment method');
        }

        try {
            $expirationDate = CarbonImmutable::parse($dateOfExpiration);
            $now = CarbonImmutable::now();

            $minutesUntilExpiration = $now->diffInMinutes($expirationDate);

            return (int) $minutesUntilExpiration;
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid date format for PIX expiration: {$dateOfExpiration}");
        }
    }

    private function mapBrandFromResponse(string $paymentMethodId): ?CardBrand
    {
        return match($paymentMethodId) {
            'amex' => CardBrand::AMEX,
            'elo' => CardBrand::ELO,
            'hipercard' => CardBrand::HIPERCARD,
            'master' => CardBrand::MASTER,
            'visa' => CardBrand::VISA,
            default => null,
        };
    }

    private function mapCustomerFromResponse(array $customer): Customer
    {
        $document = new Document(
            type: DocumentType::from($customer['identification']['type']),
            value: $customer['identification']['number'],
        );

        return new Customer(
            id: $customer['id'],
            email: $customer['email'],
            document: $document,
        );
    }
}

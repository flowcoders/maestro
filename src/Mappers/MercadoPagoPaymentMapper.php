<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Mappers;

use DateTimeImmutable;
use Flowcoders\Maestro\Contracts\PaymentMapperInterface;
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
            'amount' => $refundRequest->amount !== null ? $refundRequest->amount / 100 : null,
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

        $money = new Money(
            amount: (int) ($response['transaction_amount'] * 100),
            currency: Currency::from($response['currency_id']),
        );

        return new PaymentResponse(
            id: (string) $response['id'],
            status: $this->mapStatusFromResponse($response['status']),
            money: $money,
            description: $response['description'] ?? null,
            customer: $customer,
            externalReference: $response['external_reference'] ?? null,
            paymentMethod: $response['payment_method_id'] ?? null,
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
                ? new DateTimeImmutable($response['date_created'])
                : null,
            updatedAt: isset($response['date_last_updated'])
                ? new DateTimeImmutable($response['date_last_updated'])
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

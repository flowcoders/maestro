<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Mappers;

use Carbon\Carbon;
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
use Flowcoders\Maestro\ValueObjects\Document;
use Flowcoders\Maestro\ValueObjects\Money;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\CreditCard;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\Pix;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\BankSlip;

class AsaasPaymentMapper implements PaymentMapperInterface
{
    public function mapPaymentRequest(PaymentRequest $paymentRequest): array
    {
        $data = [
            'customer' => $this->getCustomerId($paymentRequest->customer),
            'value' => $paymentRequest->money->amount / 100,
            'description' => $paymentRequest->description,
            'externalReference' => $paymentRequest->externalReference,
        ];

        $data['billingType'] = $this->mapBillingType($paymentRequest->paymentMethod);

        if ($paymentRequest->installments > 1) {
            $data['installmentCount'] = $paymentRequest->installments;
            $data['installmentValue'] = $paymentRequest->money->amount / 100 / $paymentRequest->installments;
            unset($data['value']);
        }

        if ($paymentRequest->paymentMethod instanceof CreditCard) {
            if ($paymentRequest->token !== null) {
                $data['creditCardToken'] = $paymentRequest->token;
            } else {
                $data['creditCard'] = $this->mapCreditCard($paymentRequest->paymentMethod);
                $data['creditCardHolderInfo'] = $this->mapCreditCardHolderInfo($paymentRequest->customer);
            }
            $data['remoteIp'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }

        if ($paymentRequest->paymentMethod instanceof Pix) {
            $data['dueDate'] = $paymentRequest->paymentMethod->getExpiresAt()->format('Y-m-d');
        }

        if ($paymentRequest->notificationUrl !== null) {
            $data['notificationUrl'] = $paymentRequest->notificationUrl;
        }

        if ($paymentRequest->metadata !== null) {
            $data['metadata'] = $paymentRequest->metadata;
        }

        return array_filter($data, fn ($value) => $value !== null);
    }

    public function mapRefundPaymentRequest(RefundRequest $refundRequest): array
    {
        $data = [];

        if ($refundRequest->money !== null) {
            $data['value'] = $refundRequest->money->amount / 100;
        }

        if ($refundRequest->reason !== null) {
            $data['description'] = $refundRequest->reason;
        }

        return array_filter($data, fn ($value) => $value != null);
    }

    public function mapPaymentResponse(array $response): PaymentResponse
    {
        $customer = null;
        if (isset($response['customer'])) {
            $customer = $this->mapCustomerFromResponse($response);
        }

        $money = new Money(
            amount: (int) (($response['value'] ?? $response['totalValue'] ?? 0) * 100),
            currency: Currency::BRL,
        );

        $paymentMethod = $this->mapPaymentMethodFromResponse($response);

        return new PaymentResponse(
            id: $response['id'],
            status: $this->mapStatusFromResponse($response['status'] ?? ''),
            money: $money,
            description: $response['description'] ?? null,
            customer: $customer,
            externalReference: $response['externalReference'] ?? null,
            paymentMethod: $paymentMethod,
            capture: true,
            statementDescriptor: null,
            installments: $response['installmentCount'] ?? 1,
            notificationUrl: $response['notificationUrl'] ?? null,
            metadata: $response['metadata'] ?? null,
            pspResponse: $response,
            error: $response['errors'][0]['description'] ?? null,
            errorCode: $response['errors'][0]['code'] ?? null,
            createdAt: isset($response['dateCreated'])
                ? new Carbon($response['dateCreated'])
                : null,
            updatedAt: isset($response['lastUpdated'])
                ? new Carbon($response['lastUpdated'])
                : null,
        );
    }

    public function mapRefundResponse(array $response): RefundResponse
    {
        return new RefundResponse(
            id: $response['id'],
            paymentId: $response['payment'] ?? '',
            amount: (int) (($response['value'] ?? 0) * 100),
            status: $this->mapRefundStatusFromResponse($response['status'] ?? ''),
            reason: $response['description'] ?? null,
            metadata: $response['metadata'] ?? null,
            pspResponse: $response,
            error: $response['errors'][0]['description'] ?? null,
            errorCode: $response['errors'][0]['code'] ?? null,
            createdAt: isset($response['dateCreated'])
                ? new Carbon($response['dateCreated'])
                : null,
        );
    }

    private function mapBillingType(PaymentMethodInterface $paymentMethod): string
    {
        if ($paymentMethod instanceof Pix) {
            return 'PIX';
        }

        if ($paymentMethod instanceof CreditCard) {
            return 'CREDIT_CARD';
        }

        if ($paymentMethod instanceof BankSlip) {
            return 'BOLETO';
        }

        return match($paymentMethod->getType()) {
            PaymentMethod::PIX->value => 'PIX',
            PaymentMethod::CREDIT_CARD->value => 'CREDIT_CARD',
            PaymentMethod::BANK_SLIP->value => 'BOLETO',
            default => throw new \InvalidArgumentException("Unsupported payment method: {$paymentMethod->getType()}")
        };
    }

    private function mapCreditCard(CreditCard $creditCard): array
    {
        return [
             'number' => $creditCard->number,
             'holderName' => $creditCard->holderName,
             'expiryMonth' => str_pad((string) $creditCard->expiryMonth, 2, '0', STR_PAD_LEFT),
             'expiryYear' => (string) $creditCard->expiryYear,
             'cvv' => $creditCard->cvv,
         ];
    }

    private function mapCreditCardHolderInfo(Customer $customer): array
    {
        $data = [
            'name' => trim(($customer->firstName ?? '') . ' ' . ($customer->lastName ?? '')),
            'email' => $customer->email,
            'cpfCnpj' => $customer->document->value,
            'phone' => $customer->phone !== null ? $customer->phone->number : '',
            'mobilePhone' => $customer->phone !== null ? $customer->phone->number : '',
        ];

        if ($customer->address !== null) {
            $data['postalCode'] = $customer->address->postalCode;
            $data['address'] = $customer->address->streetLine1;
            $data['addressNumber'] = $customer->address->streetLine2 ?? 's/n';
            $data['complement'] = $customer->address->complement ?? '';
            $data['province'] = $customer->address->neighborhood ?? '';
        }

        return array_filter($data, fn ($value) => $value !== '');
    }

    private function mapStatusFromResponse(string $status): PaymentStatus
    {
        return match (strtoupper($status)) {
            'PENDING' => PaymentStatus::PENDING,
            'RECEIVED', 'CONFIRMED' => PaymentStatus::APPROVED,
            'OVERDUE' => PaymentStatus::OVERDUE,
            'REFUNDED' => PaymentStatus::REFUNDED,
            'CANCELED' => PaymentStatus::CANCELED,
            'RECEIVED_IN_CASH' => PaymentStatus::APPROVED,
            'REFUND_REQUESTED' => PaymentStatus::REFUND_REQUESTED,
            'REFUND_IN_PROGRESS' => PaymentStatus::REFUND_IN_PROGRESS,
            'CHARGEBACK_REQUESTED' => PaymentStatus::CHARGED_BACK,
            'CHARGEBACK_DISPUTE' => PaymentStatus::IN_DISPUTE,
            'AWAITING_CHARGEBACK_REVERSAL' => PaymentStatus::IN_DISPUTE,
            'DUNNING_REQUESTED' => PaymentStatus::IN_PROCESS,
            'DUNNING_RECEIVED' => PaymentStatus::APPROVED,
            'AWAITING_RISK_ANALYSIS' => PaymentStatus::IN_ANALYSIS,
            default => PaymentStatus::PENDING,
        };
    }

    private function mapRefundStatusFromResponse(string $status): RefundStatus
    {
        return match (strtoupper($status)) {
            'PENDING' => RefundStatus::PENDING,
            'CANCELLED' => RefundStatus::CANCELED,
            'PROCESSING' => RefundStatus::IN_PROCESS,
            'SUCCESS' => RefundStatus::APPROVED,
            'DONE' => RefundStatus::APPROVED,
            default => RefundStatus::PENDING,
        };
    }

    private function mapPaymentMethodFromResponse(array $response): ?PaymentMethodInterface
    {
        $billingType = $response['billingType'] ?? null;

        if ($billingType === null) {
            return null;
        }

        if ($billingType === 'PIX') {
            $pixQrCode = $response['pixQrCodeId'] ?? null;
            $qrCode = null;
            $qrCodeBase64 = null;

            if ($pixQrCode) {
                $qrCode = $response['pixCopyAndPaste'] ?? null;
                $qrCodeBase64 = $response['pixQrCodeBase64'] ?? null;
            }

            return new Pix(
                expiresAt: $response['dueDate'] ?? null,
                qrCode: $qrCode,
                qrCodeBase64: $qrCodeBase64,
                qrCodeUrl: null
            );
        }

        if ($billingType === 'CREDIT_CARD') {
            $creditCardBrand = $response['creditCard']['creditCardBrand'] ?? null;
            $brand = $creditCardBrand ? $this->mapBrandFromResponse($creditCardBrand) : null;

            return new CreditCard(
                holderName: $response['creditCard']['creditCardHolderName'] ?? null,
                expiryMonth: null,
                expiryYear: null,
                brand: $brand,
                lastFourDigits: $response['creditCard']['creditCardNumber'] ?? null,
            );
        }

        if ($billingType === 'BOLETO') {
            $bankSlipUrl = $response['bankSlipUrl'] ?? null;
            $digitableLine = $response['identificationField'] ?? null;

            return new BankSlip(
                bankSlipUrl: $bankSlipUrl,
                digitableLine: $digitableLine,
                barcode: $response['barcode'] ?? null,
            );
        }

        return null;
    }

    private function mapBrandFromResponse(string $brand): ?CardBrand
    {
        return match(strtoupper($brand)) {
            'MASTERCARD' => CardBrand::MASTER,
            'VISA' => CardBrand::VISA,
            'AMEX', 'AMERICAN EXPRESS' => CardBrand::AMEX,
            'ELO' => CardBrand::ELO,
            'HIPERCARD' => CardBrand::HIPERCARD,
            'DINERS', 'DINERS CLUB' => CardBrand::DINERS,
            'DISCOVER' => CardBrand::DISCOVER,
            'AURA' => CardBrand::AURA,
            'JCB' => CardBrand::JCB,
            default => null,
        };
    }

    private function mapCustomerFromResponse(array $response): Customer
    {
        $document = null;
        if (isset($response['cpfCnpj'])) {
            $documentType = strlen($response['cpfCnpj']) === 11 ? DocumentType::CPF : DocumentType::CNPJ;
            $document = new Document(
                type: $documentType,
                value: $response['cpfCnpj'],
            );
        }

        $email = $response['email'] ?? null;

        return new Customer(
            id: $response['customer'] ?? $response['id'] ?? null,
            firstName: $response['name'] ?? null,
            email: $email,
            documentType: $document?->type,
            documentValue: $document?->value,
        );
    }

    private function getCustomerId(Customer $customer): string
    {
        return $customer->id ?? throw new \InvalidArgumentException('Customer ID is required for Asaas payments.');
    }
}

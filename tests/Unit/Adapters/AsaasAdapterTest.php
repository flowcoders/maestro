<?php

declare(strict_types=1);

use Carbon\Carbon;
use Flowcoders\Maestro\Adapters\AsaasAdapter;
use Flowcoders\Maestro\Contracts\HttpClientInterface;
use Flowcoders\Maestro\DTOs\Customer;
use Flowcoders\Maestro\DTOs\PaymentRequest;
use Flowcoders\Maestro\DTOs\PaymentResponse;
use Flowcoders\Maestro\DTOs\RefundRequest;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\DocumentType;
use Flowcoders\Maestro\Enums\PaymentStatus;
use Flowcoders\Maestro\Enums\RefundStatus;
use Flowcoders\Maestro\Exceptions\PaymentException;
use Flowcoders\Maestro\Mappers\AsaasPaymentMapper;
use Flowcoders\Maestro\ValueObjects\Document;
use Flowcoders\Maestro\ValueObjects\Email;
use Flowcoders\Maestro\ValueObjects\Money;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\CreditCard;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\Pix;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\BankSlip;

beforeEach(function () {
    $this->httpClient = Mockery::mock(HttpClientInterface::class);
    $this->mapper = new AsaasPaymentMapper();
    $this->adapter = new AsaasAdapter($this->httpClient, $this->mapper);

    Carbon::setTestNow('2024-01-15 10:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
    Mockery::close();
});

it('creates a PIX payment successfully', function () {
    $customer = new Customer(
        id: 'cus_000005219613',
        email: new Email('test@example.com'),
        document: new Document(DocumentType::CPF, '12345678901'),
        firstName: 'John',
        lastName: 'Doe'
    );

    $paymentRequest = new PaymentRequest(
        money: new Money(10090, Currency::BRL),
        customer: $customer,
        paymentMethod: new Pix(expiresAt: 60),
        description: 'Test PIX payment',
        externalReference: 'REF123'
    );

    $asaasResponse = [
        'id' => 'pay_0876543210',
        'status' => 'PENDING',
        'value' => 100.90,
        'billingType' => 'PIX',
        'description' => 'Test PIX payment',
        'externalReference' => 'REF123',
        'customer' => 'cus_000005219613',
        'dateCreated' => '2024-01-15T10:00:00.000-03:00',
        'pixQrCodeId' => 'qrc_123456789',
        'pixCopyAndPaste' => '00020126580014br.gov.bcb.pix013612345678',
        'pixQrCodeBase64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
    ];

    $this->httpClient->shouldReceive('post')
        ->once()
        ->with('/payments', Mockery::type('array'), Mockery::type('array'))
        ->andReturn(new \Flowcoders\Maestro\DTOs\HttpResponseDTO(
            data: $asaasResponse,
            statusCode: 200,
            headers: [],
            error: null
        ));

    $response = $this->adapter->createPayment($paymentRequest);

    expect($response)->toBeInstanceOf(PaymentResponse::class);
    expect($response->id)->toBe('pay_0876543210');
    expect($response->status)->toBe(PaymentStatus::PENDING);
    expect($response->money->amount)->toBe(10090);
    expect($response->paymentMethod)->toBeInstanceOf(Pix::class);
    expect($response->paymentMethod->qrCode)->toBe('00020126580014br.gov.bcb.pix013612345678');
});

it('creates a credit card payment successfully', function () {
    $customer = new Customer(
        id: 'cus_000005219613',
        email: new Email('test@example.com'),
        document: new Document(DocumentType::CPF, '12345678901'),
        firstName: 'John',
        lastName: 'Doe'
    );

    $creditCard = new CreditCard(
        holderName: 'John Doe',
        expiryMonth: 12,
        expiryYear: 2025
    );

    $paymentRequest = new PaymentRequest(
        money: new Money(50000, Currency::BRL),
        customer: $customer,
        paymentMethod: $creditCard,
        description: 'Test Credit Card payment',
        installments: 3
    );

    $asaasResponse = [
        'id' => 'pay_0876543211',
        'status' => 'CONFIRMED',
        'value' => 500.00,
        'billingType' => 'CREDIT_CARD',
        'description' => 'Test Credit Card payment',
        'customer' => 'cus_000005219613',
        'installmentCount' => 3,
        'dateCreated' => '2024-01-15T10:00:00.000-03:00',
        'creditCard' => [
            'creditCardHolderName' => 'John Doe',
            'creditCardNumber' => '1111',
            'creditCardBrand' => 'VISA',
        ],
    ];

    $this->httpClient->shouldReceive('post')
        ->once()
        ->with('/payments', Mockery::type('array'), Mockery::type('array'))
        ->andReturn(new \Flowcoders\Maestro\DTOs\HttpResponseDTO(
            data: $asaasResponse,
            statusCode: 200,
            headers: [],
            error: null
        ));

    $response = $this->adapter->createPayment($paymentRequest);

    expect($response)->toBeInstanceOf(PaymentResponse::class);
    expect($response->id)->toBe('pay_0876543211');
    expect($response->status)->toBe(PaymentStatus::APPROVED);
    expect($response->money->amount)->toBe(50000);
    expect($response->installments)->toBe(3);
    expect($response->paymentMethod)->toBeInstanceOf(CreditCard::class);
    expect($response->paymentMethod->lastFourDigits)->toBe('1111');
});

it('creates a bank slip payment successfully', function () {
    $customer = new Customer(
        id: 'cus_000005219613',
        email: new Email('test@example.com'),
        document: new Document(DocumentType::CPF, '12345678901'),
        firstName: 'John',
        lastName: 'Doe'
    );

    $paymentRequest = new PaymentRequest(
        money: new Money(25000, Currency::BRL),
        customer: $customer,
        paymentMethod: new BankSlip(),
        description: 'Test Bank Slip payment'
    );

    $asaasResponse = [
        'id' => 'pay_0876543212',
        'status' => 'PENDING',
        'value' => 250.00,
        'billingType' => 'BOLETO',
        'description' => 'Test Bank Slip payment',
        'customer' => 'cus_000005219613',
        'dateCreated' => '2024-01-15T10:00:00.000-03:00',
        'bankSlipUrl' => 'https://asaas.com/b/pdf/0876543212',
        'identificationField' => '34191.09008 76543.212034 56789.012345 6 78900000025000',
        'barcode' => '34196789000000250001090087654321203456789012',
    ];

    $this->httpClient->shouldReceive('post')
        ->once()
        ->with('/payments', Mockery::type('array'), Mockery::type('array'))
        ->andReturn(new \Flowcoders\Maestro\DTOs\HttpResponseDTO(
            data: $asaasResponse,
            statusCode: 200,
            headers: [],
            error: null
        ));

    $response = $this->adapter->createPayment($paymentRequest);

    expect($response)->toBeInstanceOf(PaymentResponse::class);
    expect($response->id)->toBe('pay_0876543212');
    expect($response->status)->toBe(PaymentStatus::PENDING);
    expect($response->money->amount)->toBe(25000);
    expect($response->paymentMethod)->toBeInstanceOf(BankSlip::class);
    expect($response->paymentMethod->bankSlipUrl)->toBe('https://asaas.com/b/pdf/0876543212');
    expect($response->paymentMethod->digitableLine)->toBe('34191.09008 76543.212034 56789.012345 6 78900000025000');
});

it('gets a payment successfully', function () {
    $paymentId = 'pay_0876543210';

    $asaasResponse = [
        'id' => $paymentId,
        'status' => 'RECEIVED',
        'value' => 100.90,
        'billingType' => 'PIX',
        'description' => 'Test payment',
        'customer' => 'cus_000005219613',
        'dateCreated' => '2024-01-15T10:00:00.000-03:00',
        'dateReceived' => '2024-01-15T10:30:00.000-03:00',
    ];

    $this->httpClient->shouldReceive('get')
        ->once()
        ->with("/payments/{$paymentId}")
        ->andReturn(new \Flowcoders\Maestro\DTOs\HttpResponseDTO(
            data: $asaasResponse,
            statusCode: 200,
            headers: [],
            error: null
        ));

    $response = $this->adapter->getPayment($paymentId);

    expect($response)->toBeInstanceOf(PaymentResponse::class);
    expect($response->id)->toBe($paymentId);
    expect($response->status)->toBe(PaymentStatus::APPROVED);
});

it('cancels a payment successfully', function () {
    $paymentId = 'pay_0876543210';

    $asaasResponse = [
        'id' => $paymentId,
        'status' => 'CANCELED',
        'deleted' => true,
    ];

    $this->httpClient->shouldReceive('delete')
        ->once()
        ->with("/payments/{$paymentId}")
        ->andReturn(new \Flowcoders\Maestro\DTOs\HttpResponseDTO(
            data: $asaasResponse,
            statusCode: 200,
            headers: [],
            error: null
        ));

    $response = $this->adapter->cancelPayment($paymentId);

    expect($response)->toBeInstanceOf(PaymentResponse::class);
    expect($response->id)->toBe($paymentId);
    expect($response->status)->toBe(PaymentStatus::CANCELED);
});

it('refunds a payment successfully', function () {
    $paymentId = 'pay_0876543210';

    $refundRequest = new RefundRequest(
        paymentId: $paymentId,
        money: new Money(5000, Currency::BRL),
        reason: 'Customer request'
    );

    $asaasResponse = [
        'id' => 'ref_123456789',
        'payment' => $paymentId,
        'value' => 50.00,
        'status' => 'SUCCESS',
        'description' => 'Customer request',
        'dateCreated' => '2024-01-15T11:00:00.000-03:00',
    ];

    $this->httpClient->shouldReceive('post')
        ->once()
        ->with("/payments/{$paymentId}/refund", Mockery::type('array'))
        ->andReturn(new \Flowcoders\Maestro\DTOs\HttpResponseDTO(
            data: $asaasResponse,
            statusCode: 200,
            headers: [],
            error: null
        ));

    $response = $this->adapter->refundPayment($refundRequest);

    expect($response->id)->toBe('ref_123456789');
    expect($response->paymentId)->toBe($paymentId);
    expect($response->amount)->toBe(5000);
    expect($response->status)->toBe(RefundStatus::APPROVED);
    expect($response->reason)->toBe('Customer request');
});

it('throws exception when payment creation fails', function () {
    $customer = new Customer(
        id: 'cus_000005219613',
        email: new Email('test@example.com'),
        document: new Document(DocumentType::CPF, '12345678901')
    );

    $paymentRequest = new PaymentRequest(
        money: new Money(10090, Currency::BRL),
        customer: $customer,
        paymentMethod: new Pix(),
        description: 'Test payment'
    );

    $this->httpClient->shouldReceive('post')
        ->once()
        ->andReturn(new \Flowcoders\Maestro\DTOs\HttpResponseDTO(
            data: [],
            statusCode: 400,
            headers: [],
            error: 'Invalid request'
        ));

    expect(fn () => $this->adapter->createPayment($paymentRequest))
        ->toThrow(PaymentException::class, 'Failed to create payment: Invalid request');
});

it('throws exception when getting payment fails', function () {
    $paymentId = 'pay_invalid';

    $this->httpClient->shouldReceive('get')
        ->once()
        ->with("/payments/{$paymentId}")
        ->andReturn(new \Flowcoders\Maestro\DTOs\HttpResponseDTO(
            data: [],
            statusCode: 404,
            headers: [],
            error: 'Payment not found'
        ));

    expect(fn () => $this->adapter->getPayment($paymentId))
        ->toThrow(PaymentException::class, 'Failed to get payment: Payment not found');
});

it('uses existing customer when found by document', function () {
    $customer = new Customer(
        email: new Email('existing@example.com'),
        document: new Document(DocumentType::CPF, '11111111111'),
        firstName: 'Existing',
        lastName: 'Customer'
    );

    $paymentRequest = new PaymentRequest(
        money: new Money(5000, Currency::BRL),
        customer: $customer,
        paymentMethod: new Pix(),
        description: 'Test with existing customer'
    );

    $searchResponse = [
        'data' => [
            [
                'id' => 'cus_existing_123',
                'name' => 'Existing Customer',
                'email' => 'existing@example.com',
                'cpfCnpj' => '11111111111',
            ],
        ],
        'hasMore' => false,
    ];

    $paymentResponse = [
        'id' => 'pay_with_existing',
        'status' => 'PENDING',
        'value' => 50.00,
        'billingType' => 'PIX',
        'description' => 'Test with existing customer',
        'customer' => 'cus_existing_123',
        'dateCreated' => '2024-01-15T10:00:00.000-03:00',
    ];

    $this->httpClient->shouldReceive('get')
        ->once()
        ->with('/customers', ['cpfCnpj' => '11111111111'])
        ->andReturn(new \Flowcoders\Maestro\DTOs\HttpResponseDTO(
            data: $searchResponse,
            statusCode: 200,
            headers: [],
            error: null
        ));

    $this->httpClient->shouldReceive('post')
        ->once()
        ->with('/payments', Mockery::type('array'), Mockery::type('array'))
        ->andReturn(new \Flowcoders\Maestro\DTOs\HttpResponseDTO(
            data: $paymentResponse,
            statusCode: 200,
            headers: [],
            error: null
        ));

    $response = $this->adapter->createPayment($paymentRequest);

    expect($response)->toBeInstanceOf(PaymentResponse::class);
    expect($response->id)->toBe('pay_with_existing');
});

it('creates customer automatically when not provided', function () {
    $customer = new Customer(
        email: new Email('newcustomer@example.com'),
        document: new Document(DocumentType::CPF, '98765432101'),
        firstName: 'Jane',
        lastName: 'Smith'
    );

    $paymentRequest = new PaymentRequest(
        money: new Money(10000, Currency::BRL),
        customer: $customer,
        paymentMethod: new Pix(),
        description: 'Test payment with customer creation'
    );

    $customerResponse = [
        'id' => 'cus_987654321',
        'name' => 'Jane Smith',
        'email' => 'newcustomer@example.com',
        'cpfCnpj' => '98765432101',
    ];

    $paymentResponse = [
        'id' => 'pay_123456789',
        'status' => 'PENDING',
        'value' => 100.00,
        'billingType' => 'PIX',
        'description' => 'Test payment with customer creation',
        'customer' => 'cus_987654321',
        'dateCreated' => '2024-01-15T10:00:00.000-03:00',
    ];

    $this->httpClient->shouldReceive('get')
        ->once()
        ->with('/customers', ['cpfCnpj' => '98765432101'])
        ->andReturn(new \Flowcoders\Maestro\DTOs\HttpResponseDTO(
            data: ['data' => [], 'hasMore' => false],
            statusCode: 200,
            headers: [],
            error: null
        ));

    $this->httpClient->shouldReceive('post')
        ->once()
        ->with('/customers', Mockery::type('array'))
        ->andReturn(new \Flowcoders\Maestro\DTOs\HttpResponseDTO(
            data: $customerResponse,
            statusCode: 200,
            headers: [],
            error: null
        ));

    $this->httpClient->shouldReceive('post')
        ->once()
        ->with('/payments', Mockery::type('array'), Mockery::type('array'))
        ->andReturn(new \Flowcoders\Maestro\DTOs\HttpResponseDTO(
            data: $paymentResponse,
            statusCode: 200,
            headers: [],
            error: null
        ));

    $response = $this->adapter->createPayment($paymentRequest);

    expect($response)->toBeInstanceOf(PaymentResponse::class);
    expect($response->id)->toBe('pay_123456789');
    expect($response->status)->toBe(PaymentStatus::PENDING);
});

<?php

declare(strict_types=1);

use Flowcoders\Maestro\Adapters\MercadoPagoAdapter;
use Flowcoders\Maestro\Contracts\HttpClientInterface;
use Flowcoders\Maestro\DTOs\CreatePaymentDTO;
use Flowcoders\Maestro\DTOs\CustomerDTO;
use Flowcoders\Maestro\DTOs\HttpResponseDTO;
use Flowcoders\Maestro\DTOs\PaymentResponseDTO;
use Flowcoders\Maestro\DTOs\RefundPaymentDTO;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\PaymentStatus;
use Flowcoders\Maestro\Mappers\MercadoPagoPaymentMapper;
use Flowcoders\Maestro\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class MercadoPagoAdapterTest extends TestCase
{
    private MercadoPagoAdapter $adapter;
    private HttpClientInterface&MockObject $mockHttpClient;
    private MercadoPagoPaymentMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHttpClient = $this->createMock(HttpClientInterface::class);
        $this->mapper = new MercadoPagoPaymentMapper();

        $this->adapter = new MercadoPagoAdapter(
            httpClient: $this->mockHttpClient,
            mapper: $this->mapper,
            credentials: [
                'access_token' => 'TEST_TOKEN',
                'sandbox' => true,
            ]
        );
    }

    public function test_can_create_payment_successfully(): void
    {
        // Arrange
        $paymentDto = new CreatePaymentDTO(
            amount: 10000,
            currency: Currency::BRL,
            description: 'Test payment',
            customer: new CustomerDTO(
                email: 'test@example.com',
                firstName: 'John',
                lastName: 'Doe'
            ),
            paymentMethodId: 'pix'
        );

        $expectedResponse = [
            'id' => '12345',
            'status' => 'approved',
            'transaction_amount' => 10000,
            'currency_id' => 'BRL',
            'description' => 'Test payment',
            'payment_method_id' => 'pix',
            'date_created' => '2023-12-01T10:00:00.000Z',
            'date_last_updated' => '2023-12-01T10:00:00.000Z',
            'payer' => [
                'email' => 'test@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
        ];

        $this->mockHttpClient
            ->expects($this->once())
            ->method('post')
            ->with('/v1/payments', $this->isType('array'), $this->isType('array'))
            ->willReturn(new HttpResponseDTO(
                data: $expectedResponse,
                statusCode: 201
            ));

        // Act
        $result = $this->adapter->createPayment($paymentDto);

        // Assert
        $this->assertInstanceOf(PaymentResponseDTO::class, $result);
        $this->assertEquals('12345', $result->id);
        $this->assertEquals(PaymentStatus::Approved, $result->status);
        $this->assertEquals(10000, $result->amount); // Expected in cents
        $this->assertEquals(Currency::BRL, $result->currency);
        $this->assertEquals('Test payment', $result->description);
    }

    public function test_can_get_payment_successfully(): void
    {
        // Arrange
        $paymentId = '12345';
        $expectedResponse = [
            'id' => $paymentId,
            'status' => 'approved',
            'transaction_amount' => 100.00, // API returns decimal
            'currency_id' => 'BRL',
            'description' => 'Test payment',
        ];

        $this->mockHttpClient
            ->expects($this->once())
            ->method('get')
            ->with("/v1/payments/{$paymentId}")
            ->willReturn(new HttpResponseDTO(
                data: $expectedResponse,
                statusCode: 200
            ));

        // Act
        $result = $this->adapter->getPayment($paymentId);

        // Assert
        $this->assertInstanceOf(PaymentResponseDTO::class, $result);
        $this->assertEquals($paymentId, $result->id);
        $this->assertEquals(PaymentStatus::Approved, $result->status);
    }

    public function test_can_cancel_payment_successfully(): void
    {
        // Arrange
        $paymentId = '12345';
        $expectedResponse = [
            'id' => $paymentId,
            'status' => 'cancelled',
            'transaction_amount' => 100.00, // API returns decimal
            'currency_id' => 'BRL',
            'description' => 'Test payment',
        ];

        $this->mockHttpClient
            ->expects($this->once())
            ->method('put')
            ->with("/v1/payments/{$paymentId}", ['status' => 'cancelled'])
            ->willReturn(new HttpResponseDTO(
                data: $expectedResponse,
                statusCode: 200
            ));

        // Act
        $result = $this->adapter->cancelPayment($paymentId);

        // Assert
        $this->assertInstanceOf(PaymentResponseDTO::class, $result);
        $this->assertEquals($paymentId, $result->id);
        $this->assertEquals(PaymentStatus::Cancelled, $result->status);
    }

    public function test_can_refund_payment_successfully(): void
    {
        // Arrange
        $refundDto = new RefundPaymentDTO(
            paymentId: '12345',
            amount: 5000,
            reason: 'Customer request'
        );

        $refundResponse = [
            'id' => '67890',
            'payment_id' => '12345',
            'amount' => 50.00, // API returns decimal
            'status' => 'approved',
        ];

        $updatedPaymentResponse = [
            'id' => '12345',
            'status' => 'refunded',
            'transaction_amount' => 100.00, // API returns decimal
            'currency_id' => 'BRL',
            'description' => 'Test payment',
        ];

        // Mock both POST (refund) and GET (updated payment) calls
        $this->mockHttpClient
            ->method('post')
            ->willReturn(new HttpResponseDTO(data: $refundResponse, statusCode: 201));

        $this->mockHttpClient
            ->method('get')
            ->willReturn(new HttpResponseDTO(data: $updatedPaymentResponse, statusCode: 200));

        // Act
        $result = $this->adapter->refundPayment($refundDto);

        // Assert
        $this->assertInstanceOf(PaymentResponseDTO::class, $result);
        $this->assertEquals('12345', $result->id);
        $this->assertEquals(PaymentStatus::Refunded, $result->status);
    }
}

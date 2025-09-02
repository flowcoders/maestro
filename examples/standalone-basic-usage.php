<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Flowcoders\Maestro\Adapters\MercadoPagoAdapter;
use Flowcoders\Maestro\DTOs\Customer;
use Flowcoders\Maestro\DTOs\PaymentRequest;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\DocumentType;
use Flowcoders\Maestro\Http\BaseHttpClient;
use Flowcoders\Maestro\Mappers\MercadoPagoPaymentMapper;
use Flowcoders\Maestro\Utils\TimezoneHelper;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\Pix;
use Illuminate\Http\Client\Factory as HttpFactory;

// Simple .env file loader
function loadEnvFile(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }

        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            $value = trim($value, '"\'');

            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Example of basic payment creation without Laravel facades
function createBasicPaymentStandalone(): void
{
    // Load .env file from the project root
    loadEnvFile(__DIR__ . '/../.env');

    // Configure timezone for standalone usage (Brazil timezone)
    // In Laravel apps, this will be automatically configured from app.timezone
    TimezoneHelper::setTimezone('America/Sao_Paulo');

    // You need to set your MercadoPago access token here
    // For testing, use a TEST- token, for production use an APP- token
    $accessToken = $_ENV['MERCADOPAGO_ACCESS_TOKEN'] ?? 'TEST-your-test-token-here';

    if ($accessToken === 'TEST-your-test-token-here') {
        echo "⚠️  Please set your MERCADOPAGO_ACCESS_TOKEN in your .env file or update the \$accessToken variable in this script.\n";
        echo "You can get a test token from: https://www.mercadopago.com.br/developers/panel\n";
        echo "Create a .env file in the project root with: MERCADOPAGO_ACCESS_TOKEN=your-token-here\n";

        return;
    }

    // Manually create all dependencies
    $httpFactory = new HttpFactory();

    $httpClient = new BaseHttpClient(
        httpFactory: $httpFactory,
        baseUrl: 'https://api.mercadopago.com',
        defaultHeaders: [
            'Content-Type' => 'application/json',
            'User-Agent' => 'Maestro-PHP-SDK/1.0',
        ],
        timeout: 30,
        bearerToken: $accessToken
    );

    $mapper = new MercadoPagoPaymentMapper();

    $maestro = new MercadoPagoAdapter(
        httpClient: $httpClient,
        mapper: $mapper
    );

    // Create customer data with simplified API (unformatted values)
    $customer = new Customer(
        id: '2626419973-6nXIjAhpZPtuhn',
        firstName: 'João',
        lastName: 'Silva',
        email: 'joaosilvatest@gmail.com',
        documentType: DocumentType::CPF,
        documentValue: '98488647093',
        phoneNumber: '5511999999999',
        postalCode: '01234567',
        streetLine1: 'Rua das Flores',
        streetLine2: '123',
        city: 'São Paulo',
        stateOrProvince: 'SP',
        countryCode: 'BR',
        neighborhood: 'Centro'
    );

    // Create a PIX payment method that expires in 1 hour using configured timezone
    $pix = new Pix(expiresAt: TimezoneHelper::now()->addHour()->toISOString());

    $paymentRequest = new PaymentRequest(
        amount: 25000,
        currency: Currency::BRL,
        paymentMethod: $pix,
        description: 'Compra de produto no e-commerce',
        customer: $customer,
        installments: 1,
        capture: true,
        externalReference: 'ORDER-12345',
        notificationUrl: 'https://your-app.com/webhooks/maestro',
        callbackUrl: 'https://your-app.com/payment/success',
        metadata: [
            'order_id' => '12345',
            'customer_id' => '67890',
        ],
        idempotencyKey: \Illuminate\Support\Str::uuid()->toString(),
    );

    try {
        $paymentResponse = $maestro->createPayment($paymentRequest);

        echo "✅ Payment created successfully!\n";
        echo "Payment ID: {$paymentResponse->id}\n";
        echo "Status: {$paymentResponse->status->value}\n";
        echo "Amount: {$paymentResponse->money->amount} {$paymentResponse->money->currency->value}\n";
        echo "Customer: {$paymentResponse->customer?->firstName} {$paymentResponse->customer?->lastName}\n";

        if ($paymentResponse->hasError()) {
            echo "⚠️ Payment has errors: {$paymentResponse->error}\n";
        }

        // Get payment details
        $fetchedPayment = $maestro->getPayment($paymentResponse->id);
        echo "📄 Fetched payment status: {$fetchedPayment->status->value}\n";

    } catch (\Exception $e) {
        echo "❌ Error creating payment: {$e->getMessage()}\n";
        echo "Error code: {$e->getCode()}\n";
        echo "Error stack trace: {$e->getTraceAsString()}\n";
    }
}

// Execute the example
createBasicPaymentStandalone();

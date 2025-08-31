<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Flowcoders\Maestro\DTOs\Customer;
use Flowcoders\Maestro\DTOs\PaymentRequest;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\DocumentType;
use Flowcoders\Maestro\Facades\Maestro;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\Pix;

function createBasicPayment(): void
{
    // Create customer using simplified API (no Value Objects needed)
    $customer = new Customer(
        firstName: 'João',
        lastName: 'Silva',
        email: 'customer@example.com',
        documentType: DocumentType::CPF,
        documentValue: '78021222018',
        phoneNumber: '5511999999999',
        postalCode: '01234567',
        streetLine1: 'Rua das Flores',
        streetLine2: '123',
        city: 'São Paulo',
        stateOrProvince: 'SP',
        countryCode: 'BR',
        neighborhood: 'Centro'
    );

    $pix = new Pix(expiresAt: (new DateTime('+1 hour'))->format('c'));

    $paymentRequest = new PaymentRequest(
        amount: 10000,
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
    );

    try {
        $paymentResponse = Maestro::createPayment($paymentRequest);

        echo "✅ Payment created successfully!\n";
        echo "Payment ID: {$paymentResponse->id}\n";
        echo "Status: {$paymentResponse->status->value}\n";
        echo "Amount: {$paymentResponse->money->amount} {$paymentResponse->money->currency->value}\n";
        echo "Customer: {$paymentResponse->customer?->firstName} {$paymentResponse->customer?->lastName}\n";

        if ($paymentResponse->hasError()) {
            echo "⚠️ Payment has errors: {$paymentResponse->error}\n";
        }

        // Get payment details
        $fetchedPayment = Maestro::getPayment($paymentResponse->id);
        echo "📄 Fetched payment status: {$fetchedPayment->status->value}\n";

    } catch (\Exception $e) {
        echo "❌ Error creating payment: {$e->getMessage()}\n";
    }
}

// Execute the example
createBasicPayment();

<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Flowcoders\Maestro\DTOs\Customer;
use Flowcoders\Maestro\DTOs\PaymentRequest;
use Flowcoders\Maestro\Enums\CountryCode;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\DocumentType;
use Flowcoders\Maestro\Facades\Maestro;
use Flowcoders\Maestro\ValueObjects\Address;
use Flowcoders\Maestro\ValueObjects\Document;
use Flowcoders\Maestro\ValueObjects\Email;
use Flowcoders\Maestro\ValueObjects\Money;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\Pix;
use Flowcoders\Maestro\ValueObjects\Phone;

// Example of basic payment creation
function createBasicPayment(): void
{
    $email = new Email('customer@example.com');
    $document = new Document(DocumentType::CPF, '78021222018');
    $phone = new Phone('+5511999999999');
    $address = new Address(
        postalCode: '01234-567',
        streetLine1: 'Rua das Flores',
        streetLine2: '123',
        city: 'São Paulo',
        stateOrProvince: 'SP',
        countryCode: CountryCode::BR,
        neighborhood: 'Centro'
    );

    $customer = new Customer(
        email: $email,
        firstName: 'João',
        lastName: 'Silva',
        document: $document,
        phone: $phone,
        address: $address
    );

    // Create a PIX payment method that expires in 1 hour
    $pix = new Pix(expiresAt: 60);

    $money = new Money(10000, Currency::BRL);

    $paymentRequest = new PaymentRequest(
        money: $money,
        paymentMethod: $pix,
        description: 'Compra de produto no e-commerce',
        installments: 1,
        capture: true,
        customer: $customer,
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
        echo "Amount: {$paymentResponse->amount} {$paymentResponse->currency->value}\n";
        echo "Customer: {$paymentResponse->customer?->getFullName()}\n";

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

<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Flowcoders\Maestro\DTOs\CreatePaymentDTO;
use Flowcoders\Maestro\DTOs\CustomerDTO;
use Flowcoders\Maestro\DTOs\AddressDTO;
use Flowcoders\Maestro\Enums\Country;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Facades\Maestro;

// Example of basic payment creation
function createBasicPayment(): void
{
    $customer = new CustomerDTO(
        email: 'customer@example.com',
        firstName: 'João',
        lastName: 'Silva',
        document: '12345678900',
        documentType: 'CPF',
        phone: '+5511999999999',
        address: new AddressDTO(
            streetName: 'Rua das Flores',
            streetNumber: '123',
            postalCode: '01234-567',
            city: 'São Paulo',
            state: 'SP',
            country: Country::BR,
            neighborhood: 'Centro'
        )
    );

    $paymentData = new CreatePaymentDTO(
        amount: 10000, // R$ 100.00 in cents
        currency: Currency::BRL,
        description: 'Compra de produto no e-commerce',
        customer: $customer,
        paymentMethod: 'pix',
        externalReference: 'ORDER-12345',
        metadata: [
            'order_id' => '12345',
            'customer_id' => '67890',
        ],
        notificationUrl: 'https://your-app.com/webhooks/maestro',
        callbackUrl: 'https://your-app.com/payment/success',
        installments: 1
    );

    try {
        $payment = Maestro::createPayment($paymentData);

        echo "✅ Payment created successfully!\n";
        echo "Payment ID: {$payment->id}\n";
        echo "Status: {$payment->status->value}\n";
        echo "Amount: {$payment->amount} {$payment->currency->value}\n";
        echo "Customer: {$payment->customer?->getFullName()}\n";

        if ($payment->hasError()) {
            echo "⚠️ Payment has errors: {$payment->error}\n";
        }

        // Get payment details
        $fetchedPayment = Maestro::getPayment($payment->id);
        echo "📄 Fetched payment status: {$fetchedPayment->status->value}\n";

    } catch (\Exception $e) {
        echo "❌ Error creating payment: {$e->getMessage()}\n";
    }
}

// Example of payment management
function managePayment(string $paymentId): void
{
    try {
        // Get payment details
        $payment = Maestro::getPayment($paymentId);
        echo "Current status: {$payment->status->value}\n";

        // Cancel payment if it's pending
        if ($payment->status->isPending()) {
            $cancelledPayment = Maestro::cancelPayment($paymentId);
            echo "Payment cancelled: {$cancelledPayment->status->value}\n";
        }

        // Refund payment if it's approved
        if ($payment->status->isApproved()) {
            $refundData = new \Flowcoders\Maestro\DTOs\RefundPaymentDTO(
                paymentId: $paymentId,
                amount: 5000, // Partial refund in cents (R$ 50.00)
                reason: 'Customer request'
            );

            $refundedPayment = Maestro::refundPayment($refundData);
            echo "Payment refunded: {$refundedPayment->status->value}\n";
        }

    } catch (\Exception $e) {
        echo "❌ Error managing payment: {$e->getMessage()}\n";
    }
}

// Run examples
echo "🎵 Maestro Payment Examples\n";
echo "==========================\n\n";

echo "1. Creating a basic payment:\n";
createBasicPayment();

echo "\n2. Managing payments:\n";
// managePayment('PAYMENT_ID_HERE'); // Uncomment and provide a real payment ID

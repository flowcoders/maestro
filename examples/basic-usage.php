<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Flowcoders\Maestro\DTOs\CreatePaymentDTO;
use Flowcoders\Maestro\DTOs\RefundPaymentDTO;
use Flowcoders\Maestro\DTOs\CustomerDTO;
use Flowcoders\Maestro\DTOs\AddressDTO;
use Flowcoders\Maestro\Enums\Country;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\DocumentType;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\PaymentMethodFactory;
use Flowcoders\Maestro\Facades\Maestro;
use DateTimeImmutable;

// Example of basic payment creation
function createBasicPayment(): void
{
    $customer = CustomerDTO::create(
        email: 'customer@example.com',
        firstName: 'João',
        lastName: 'Silva',
        document: '12345678900',
        documentType: DocumentType::CPF,
        phone: '+5511999999999',
        address: AddressDTO::create(
            postalCode: '01234-567',
            streetName: 'Rua das Flores',
            streetNumber: '123',
            city: 'São Paulo',
            state: 'SP',
            country: Country::BR,
            neighborhood: 'Centro'
        )
    );

    // Create a PIX payment method that expires in 1 hour
    $pixPaymentMethod = PaymentMethodFactory::createPixWithDuration(
        expirationMinutes: 60
    );

    $paymentData = new CreatePaymentDTO(
        amount: 10000, // R$ 100.00 in cents
        currency: Currency::BRL,
        description: 'Compra de produto no e-commerce',
        paymentMethod: $pixPaymentMethod,
        customer: $customer,
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
            $refundData = new RefundPaymentDTO(
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

// Example showing different payment methods
function paymentMethodExamples(): void
{
    echo "\n📦 Payment Method Examples\n";
    echo "==============================\n";

    $customer = CustomerDTO::create(
        email: 'customer@example.com',
        firstName: 'João',
        lastName: 'Silva',
        document: '12345678900',
        documentType: DocumentType::CPF,
        phone: '+5511999999999',
    );

    // 1. Credit Card Payment with Installments
    echo "\n💳 Credit Card Payment:\n";
    try {
        $creditCard = PaymentMethodFactory::createCreditCard(
            token: 'tok_card_12345',
            holderName: 'João Silva',
            expirationMonth: 12,
            expirationYear: 2025,
            brand: 'visa',
            lastFourDigits: '1234'
        );

        $creditCardPayment = new CreatePaymentDTO(
            amount: 24000, // R$ 240.00
            currency: Currency::BRL,
            description: 'Compra com cartão de crédito',
            paymentMethod: $creditCard,
            customer: $customer,
            installments: 3 // 3x installments
        );

        echo "✅ Credit card payment created with {$creditCardPayment->installments} installments\n";
        echo "   Requires document: " . ($creditCardPayment->requiresDocument() ? 'Yes' : 'No') . "\n";
    } catch (Exception $e) {
        echo "❌ Credit card error: {$e->getMessage()}\n";
    }

    // 2. PIX Payment
    echo "\n🟢 PIX Payment:\n";
    try {
        $pix = PaymentMethodFactory::createPixWithDuration(
            expirationMinutes: 30 // Expires in 30 minutes
        );

        $pixPayment = new CreatePaymentDTO(
            amount: 5000, // R$ 50.00
            currency: Currency::BRL,
            description: 'Pagamento via PIX',
            paymentMethod: $pix,
            customer: $customer // Required for PIX
        );

        echo "✅ PIX payment created with 30min expiration\n";
        echo "   Requires document: " . ($pixPayment->requiresDocument() ? 'Yes' : 'No') . "\n";
    } catch (Exception $e) {
        echo "❌ PIX error: {$e->getMessage()}\n";
    }

    // 3. Bank Slip Payment
    echo "\n🧾 Bank Slip Payment:\n";
    try {
        $bankSlip = PaymentMethodFactory::createBankSlipWithDays(
            expirationDays: 7,
            instructions: 'Não receber após o vencimento'
        );

        $bankSlipPayment = new CreatePaymentDTO(
            amount: 15000, // R$ 150.00
            currency: Currency::BRL,
            description: 'Pagamento via boleto bancário',
            paymentMethod: $bankSlip,
            customer: $customer // Required for bank slip
        );

        echo "✅ Bank slip payment created with 7 days expiration\n";
        echo "   Requires document: " . ($bankSlipPayment->requiresDocument() ? 'Yes' : 'No') . "\n";
    } catch (Exception $e) {
        echo "❌ Bank slip error: {$e->getMessage()}\n";
    }

    // 4. Example of validation error (PIX without customer)
    echo "\n⚠️  Validation Example (PIX without customer):\n";
    try {
        $pix = PaymentMethodFactory::createPixWithDuration(60);
        
        new CreatePaymentDTO(
            amount: 1000,
            currency: Currency::BRL,
            description: 'This will fail',
            paymentMethod: $pix,
            customer: null // This will cause validation error
        );
    } catch (Exception $e) {
        echo "❌ Expected validation error: {$e->getMessage()}\n";
    }
}

// Run examples
echo "🎵 Maestro Payment Examples\n";
echo "==========================\n\n";

echo "1. Creating a basic payment:\n";
createBasicPayment();
managePayment('payment_12345');
paymentMethodExamples();
// managePayment('PAYMENT_ID_HERE'); // Uncomment and provide a real payment ID

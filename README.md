# Maestro - Unified Payment Gateway for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/flowcoders/maestro.svg?style=flat-square)](https://packagist.org/packages/flowcoders/maestro)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/flowcoders/maestro/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/flowcoders/maestro/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/flowcoders/maestro/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/flowcoders/maestro/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/flowcoders/maestro.svg?style=flat-square)](https://packagist.org/packages/flowcoders/maestro)

**Stop rewriting payment code every time you switch payment providers.**

Maestro provides a single, consistent API for all your payment needs. Whether you're using MercadoPago today and want to add Stripe tomorrow, or need to switch providers entirely - your code stays the same.

## 🎯 Simple API Design

**No Value Objects required!** Work with simple values while Maestro handles validation and formatting internally.

```php
$customer = new Customer(
    email: 'user@example.com',
    documentType: DocumentType::CPF,
    documentValue: '12345678901',
    phoneNumber: '5511999999999',
    postalCode: '01234567',
    city: 'São Paulo',
    // ...
);

$payment = new PaymentRequest(
    amount: 25000,                 // Amount in cents
    currency: Currency::BRL,
    customer: $customer,
    // ...
);
```

### Data Format Policy

**Provide clean, unformatted data** - Maestro handles formatting internally:

- **Phone numbers**: `5511999999999` (no + or parentheses)
- **Postal codes**: `01234567` (no dashes or spaces)  
- **Documents**: `12345678901` (numbers only)
- **Emails**: `user@example.com` (simple string)

The package automatically adds proper formatting when communicating with payment providers.

## Why Maestro?

**The Problem**: Every payment provider has different APIs, data formats, and integration patterns. Switching providers means rewriting all your payment logic.

**The Solution**: Write your payment code once, use it with any provider.

```php
// This same code works with MercadoPago, Stripe, or any other provider
$pix = new Pix();

$payment = Maestro::createPayment(new PaymentRequest(
    amount: 10000,                    // R$ 100.00 in cents
    currency: Currency::BRL,
    paymentMethod: $pix,
    description: 'Product purchase',
    customer: new Customer(
        email: 'user@example.com',
        documentType: DocumentType::CPF,
        documentValue: '12345678901'
    ),
    expiresAt: now()->addHour()->toISOString()
));
```

## What's Included

- ✅ **Asaas** - Full support
- ✅ **MercadoPago** - Full support
- 🔄 **More providers coming** - Stripe, PayPal, ...
- 🛡️ **Type-safe** - Full PHP 8.3+ type declarations  
- 🧪 **Battle-tested** - Comprehensive test coverage

## Installation

```bash
composer require flowcoders/maestro
```

## Configuration

### Publishing the Config File

To customize Maestro's behavior, publish the configuration file:

```bash
php artisan vendor:publish --tag="maestro-config"
```

This creates `config/maestro.php` where you can configure:

- **Default payment provider** - Set which provider to use by default
- **Provider credentials** - Configure access tokens and settings for each provider  
- **HTTP settings** - Timeout, retry attempts, and retry delays

**Example config customization:**

```php
// config/maestro.php
return [
    'default' => 'asaas',
    
    'providers' => [
        'mercadopago' => [
            'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
        ],
        'asaas' => [
            'base_url' => env('ASAAS_BASE_URL', 'https://api-sandbox.asaas.com/v3'),
            'access_token' => env('ASAAS_ACCESS_TOKEN'),
        ],
    ],
    
    'http' => [
        'timeout' => 30,
        'retry_attempts' => 3,
    ],
];
```

## Quick Setup

1. **Add your credentials to `.env`**:
```env
ASAAS_BASE_URL=https://api-sandbox.asaas.com/v3
ASAAS_ACCESS_TOKEN=TEST-your_token_here
```

2. **Start processing payments**:
```php
use Flowcoders\Maestro\Facades\Maestro;
use Flowcoders\Maestro\ValueObjects\Money;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\Pix;

$payment = Maestro::createPayment(/* ... */);
```

That's it! No config files, no complex setup.

## Usage Examples

### Create a Payment

```php
use Flowcoders\Maestro\Facades\Maestro;
use Flowcoders\Maestro\DTOs\PaymentRequest;
use Flowcoders\Maestro\DTOs\Customer;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\Pix;
use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\Enums\DocumentType;

// Create payment with simplified API (no Value Objects needed!)
$pix = new Pix();
$customer = new Customer(
    firstName: 'John',
    lastName: 'Doe',
    email: 'customer@example.com',              
    documentType: DocumentType::CPF,
    documentValue: '12345678901',              
    phoneNumber: '5511999999999',         
    postalCode: '01234567',                    
    city: 'São Paulo'
);

// Create the payment
$payment = Maestro::createPayment(new PaymentRequest(
    amount: 10000,                              // R$ 100.00 in cents
    currency: Currency::BRL,
    paymentMethod: $pix,
    description: 'Product purchase',
    customer: $customer,
    expiresAt: now()->addHour()->toISOString()
));

// Get payment details
echo $payment->id; // Payment ID from provider
echo $payment->status->value; // 'pending', 'approved', etc.
```

### All Operations

```php
// Create payment
$payment = Maestro::createPayment($paymentRequest);

// Get payment status
$payment = Maestro::getPayment('payment_id');

// Cancel payment
$payment = Maestro::cancelPayment('payment_id');

// Refund payment (full or partial)
$refundMoney = new Money(5000, Currency::BRL); // Partial refund
$payment = Maestro::refundPayment(new RefundRequest(
    paymentId: 'payment_id',
    money: $refundMoney, // Optional: leave null for full refund
    reason: 'Customer request'
));
```

## 💰 Money Handling

Use the **Money value object** with amounts in cents:

```php
// ✅ Correct
$money = new Money(10000, Currency::BRL); // R$ 100.00

// ❌ Wrong  
amount: 100.00 // This field doesn't exist
```

Maestro automatically converts to each provider's expected format.

## Need More Examples?

Check out [`examples/basic-usage.php`](examples/basic-usage.php) for a complete working example with all features.

## Contributing

Contributions are welcome! Please see our [contributing guide](CONTRIBUTING.md).

## Testing

```bash
composer test
```

## Security

For security vulnerabilities, please email the maintainer directly instead of using the issue tracker.

## Credits

- **[Paulo Guerra](https://github.com/pvguerra)** - Creator & maintainer
- **[Edilson Fernandes](https://github.com/humble23)** - Contributor

## License

MIT License. See [LICENSE.md](LICENSE.md) for details.

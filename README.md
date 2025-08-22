# Maestro - Unified Payment Gateway for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/flowcoders/maestro.svg?style=flat-square)](https://packagist.org/packages/flowcoders/maestro)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/flowcoders/maestro/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/flowcoders/maestro/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/flowcoders/maestro/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/flowcoders/maestro/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/flowcoders/maestro.svg?style=flat-square)](https://packagist.org/packages/flowcoders/maestro)

**Stop rewriting payment code every time you switch payment providers.**

Maestro provides a single, consistent API for all your payment needs. Whether you're using MercadoPago today and want to add Stripe tomorrow, or need to switch providers entirely - your code stays the same.

## Why Maestro?

**The Problem**: Every payment provider has different APIs, data formats, and integration patterns. Switching providers means rewriting all your payment logic.

**The Solution**: Write your payment code once, use it with any provider.

```php
// This same code works with MercadoPago, Stripe, or any other provider
$money = new Money(10000, Currency::BRL); // R$ 100.00 in cents
$pix = new Pix(expiresAt: 60); // 1 hour expiration

$payment = Maestro::createPayment(new PaymentRequest(
    money: $money,
    paymentMethod: $pix,
    description: 'Product purchase',
    customer: new Customer(/* ... */),
));
```

## What's Included

- ✅ **MercadoPago** - Full support including PIX
- 🔄 **More providers coming** - Stripe, Adyen, PagSeguro
- 🛡️ **Type-safe** - Full PHP 8.3+ type declarations  
- 🧪 **Battle-tested** - Comprehensive test coverage

## Installation

```bash
composer require flowcoders/maestro
```

## Quick Setup

1. **Add your credentials to `.env`**:
```env
MERCADOPAGO_ACCESS_TOKEN=TEST-your_token_here
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
use Flowcoders\Maestro\ValueObjects\Money;
use Flowcoders\Maestro\ValueObjects\Email;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\Pix;
use Flowcoders\Maestro\Enums\Currency;

// Create payment components
$money = new Money(10000, Currency::BRL); // R$ 100.00 in cents
$pix = new Pix(expiresAt: 60); // Expires in 1 hour
$customer = new Customer(
    firstName: 'John',
    lastName: 'Doe',
    email: new Email('customer@example.com')
);

// Create the payment
$payment = Maestro::createPayment(new PaymentRequest(
    money: $money,
    paymentMethod: $pix,
    description: 'Product purchase',
    customer: $customer
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

## License

MIT License. See [LICENSE.md](LICENSE.md) for details.

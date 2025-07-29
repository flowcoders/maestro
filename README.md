# Maestro - Payment Service Provider Integration

[![Latest Version on Packagist](https://img.shields.io/packagist/v/flowcoders/maestro.svg?style=flat-square)](https://packagist.org/packages/flowcoders/maestro)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/flowcoders/maestro/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/flowcoders/maestro/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/flowcoders/maestro/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/flowcoders/maestro/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/flowcoders/maestro.svg?style=flat-square)](https://packagist.org/packages/flowcoders/maestro)

Maestro is a Laravel package that provides a unified interface for integrating multiple Payment Service Providers (PSPs). Built with SOLID principles and the Adapter pattern, it allows you to switch between different payment providers without changing your application code.

## Features

- 🔌 **Unified Interface** - Same API for all payment providers
- 🏗️ **Adapter Pattern** - Easy to extend with new providers  
- 🛡️ **Type Safety** - Full PHP 8.3+ type declarations with DTOs
- 🔧 **SOLID Principles** - Clean, maintainable architecture
- 🧪 **Well Tested** - Comprehensive test coverage
- 📋 **Laravel Integration** - Native Laravel service container support

## Supported Providers

- ✅ **MercadoPago** - Ready to use
- 🔄 **More coming soon** - Adyen, Stripe, PagSeguro...

## Installation

You can install the package via composer:

```bash
composer require flowcoders/maestro
```

Publish the config file:

```bash
php artisan vendor:publish --tag="maestro-config"
```

## Configuration

Add your payment provider credentials to your `.env` file. You can use the provided `.env.example` as a reference:

```bash
# Copy the example file to your Laravel app
cp vendor/flowcoders/maestro/.env.example .env.maestro
```

### Required Environment Variables

```env
# MercadoPago (environment auto-detected by token prefix)
MERCADOPAGO_ACCESS_TOKEN=TEST-your_test_token_here  # TEST- for sandbox, APP- for production

# Optional: Change default provider
MAESTRO_PAYMENT_PROVIDER=mercadopago
```

> 💡 **Tip**: Check the `.env.example` file in the package for a complete list of available configuration options.

## Basic Usage

### Using the Facade

```php
use Flowcoders\Maestro\Facades\Maestro;
use Flowcoders\Maestro\DTOs\PaymentDTO;
use Flowcoders\Maestro\DTOs\CustomerDTO;
use Flowcoders\Maestro\Enums\Currency;

// Create a payment
$payment = Maestro::createPayment(new PaymentDTO(
    amount: 10000, // Amount in cents (R$ 100.00)
    currency: Currency::BRL,
    description: 'Product purchase',
    customer: new CustomerDTO(
        email: 'customer@example.com',
        firstName: 'John',
        lastName: 'Doe'
    ),
    paymentMethod: 'pix'
));

echo $payment->id; // Payment ID from provider
echo $payment->status->value; // 'pending', 'approved', etc.
```

### Using Dependency Injection

```php
use Flowcoders\Maestro\Contracts\PaymentServiceProviderInterface;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentServiceProviderInterface $paymentProvider
    ) {}
    
    public function store(Request $request)
    {
        $paymentData = new CreatePaymentDTO(
            amount: $request->amount,
            currency: Currency::from($request->currency),
            description: $request->description,
            // ... other fields
        );
        
        $payment = $this->paymentProvider->createPayment($paymentData);
        
        return response()->json([
            'payment_id' => $payment->id,
            'status' => $payment->status->value,
            'amount' => $payment->amount,
        ]);
    }
}
```

### Available Operations

```php
// Create payment
$payment = Maestro::createPayment($createPaymentDTO);

// Get payment details
$payment = Maestro::getPayment('payment_id');

// Cancel payment
$payment = Maestro::cancelPayment('payment_id');

// Refund payment
$payment = Maestro::refundPayment(new RefundPaymentDTO(
    paymentId: 'payment_id',
    amount: 5000, // partial refund in cents (R$ 50.00)
    reason: 'Customer request'
));
```

## Important Notes

### 💰 Monetary Values

**All monetary values in Maestro are handled as integers in cents** to avoid floating-point precision issues:

```php
// ✅ Correct - Use cents
$payment = new CreatePaymentDTO(
    amount: 10000, // R$ 100.00
    currency: Currency::BRL
);

// ❌ Incorrect - Don't use floats
$payment = new CreatePaymentDTO(
    amount: 100.00, // This will cause type errors
    currency: Currency::BRL
);
```

### 🌐 Multi-Provider Compatibility

The package automatically handles the conversion between your integer cents and each provider's expected format:

- **Your app**: `10000` (cents)
- **MercadoPago API**: `100.00` (decimal)
- **Future providers**: Handled automatically by their respective mappers

## Usage

```php
$variable = new Flowcoders\Maestro();
echo $variable->echoPhrase('Hello, Flowcoders!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Paulo Guerra](https://github.com/pauloguerra)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

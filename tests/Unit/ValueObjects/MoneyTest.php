<?php

declare(strict_types=1);

use Flowcoders\Maestro\Enums\Currency;
use Flowcoders\Maestro\ValueObjects\Money;

it('creates money with valid amount', function () {
    $money = new Money(
        amount: 1000,
        currency: Currency::BRL
    );

    expect($money->amount)->toBe(1000);
    expect($money->currency)->toBe(Currency::BRL);
});

it('accepts zero amount', function () {
    $money = new Money(
        amount: 0,
        currency: Currency::USD
    );

    expect($money->amount)->toBe(0);
});

it('accepts maximum allowed amount', function () {
    $money = new Money(
        amount: 10000000, // 100,000.00 in cents
        currency: Currency::EUR
    );

    expect($money->amount)->toBe(10000000);
});

it('works with different currencies', function (Currency $currency) {
    $money = new Money(
        amount: 5000,
        currency: $currency
    );

    expect($money->currency)->toBe($currency);
})->with([
    Currency::BRL,
    Currency::USD,
    Currency::EUR,
    Currency::ARS,
    Currency::MXN,
    Currency::COP,
    Currency::CLP,
]);

it('throws exception for negative amount', function () {
    new Money(
        amount: -1,
        currency: Currency::BRL
    );
})->throws(InvalidArgumentException::class, 'Payment amount must be greater or equal to zero');

it('throws exception for amount exceeding maximum', function () {
    new Money(
        amount: 10000001, // More than 100,000.00
        currency: Currency::BRL
    );
})->throws(InvalidArgumentException::class, 'Payment amount cannot exceed 100,000.00');

dataset('invalid_amounts', [
    -1,
    -100,
    -999999,
    10000001,
    20000000,
    99999999,
]);

it('rejects invalid amounts', function (int $amount) {
    new Money(
        amount: $amount,
        currency: Currency::BRL
    );
})->with('invalid_amounts')->throws(InvalidArgumentException::class);

dataset('valid_amounts', [
    0,
    1,
    100,
    1500,
    999999,
    5000000,
    10000000,
]);

it('accepts valid amounts', function (int $amount) {
    $money = new Money(
        amount: $amount,
        currency: Currency::USD
    );

    expect($money->amount)->toBe($amount);
})->with('valid_amounts');

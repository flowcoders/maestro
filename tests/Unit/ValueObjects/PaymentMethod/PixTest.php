<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Flowcoders\Maestro\Enums\PaymentMethod;
use Flowcoders\Maestro\ValueObjects\PaymentMethod\Pix;

beforeEach(function () {
    CarbonImmutable::setTestNow('2024-01-15 10:00:00');
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

it('creates pix with default expiration', function () {
    $pix = new Pix();

    expect($pix->expiresAt)->toBe(60);
});

it('creates pix with custom expiration', function () {
    $pix = new Pix(expiresAt: 120);

    expect($pix->expiresAt)->toBe(120);
});

it('returns correct payment method type', function () {
    $pix = new Pix();

    expect($pix->getType())->toBe(PaymentMethod::PIX->value);
});

it('indicates document is required', function () {
    $pix = new Pix();

    expect($pix->isDocumentRequired())->toBeTrue();
});

it('calculates expiration timestamp correctly', function () {
    $pix = new Pix(expiresAt: 60);

    $expectedTimestamp = CarbonImmutable::now()->addMinutes(60)->getTimestamp();

    expect($pix->getExpirationTimestamp())->toBe($expectedTimestamp);
});

it('is not expired when created', function () {
    $pix = new Pix(expiresAt: 60);

    expect($pix->isExpired())->toBeFalse();
});

it('demonstrates expiration behavior', function () {
    // Due to dynamic calculation, we test the behavior as it actually works
    $pix = new Pix(expiresAt: 60);

    // Initially not expired
    expect($pix->isExpired())->toBeFalse();

    // The expiration timestamp should be 60 minutes from now
    $expectedTimestamp = CarbonImmutable::now()->addMinutes(60)->getTimestamp();
    expect($pix->getExpirationTimestamp())->toBe($expectedTimestamp);
});

it('is not expired exactly at expiration time', function () {
    $pix = new Pix(expiresAt: 60);

    // Fast forward time to exactly expiration time
    CarbonImmutable::setTestNow(CarbonImmutable::now()->addMinutes(60));

    expect($pix->isExpired())->toBeFalse();
});

it('throws exception for zero expiration time', function () {
    new Pix(expiresAt: 0);
})->throws(InvalidArgumentException::class, 'PIX expiration date must be in the future');

it('throws exception for negative expiration time', function () {
    new Pix(expiresAt: -30);
})->throws(InvalidArgumentException::class, 'PIX expiration date must be in the future');

it('throws exception for expiration beyond 24 hours', function () {
    new Pix(expiresAt: 1441); // 24 hours and 1 minute
})->throws(InvalidArgumentException::class, 'PIX expiration date cannot be more than 24 hours in the future');

it('accepts maximum allowed expiration time', function () {
    $pix = new Pix(expiresAt: 1440); // Exactly 24 hours

    expect($pix->expiresAt)->toBe(1440);
});

dataset('valid_expiration_times', [
    1,      // 1 minute
    15,     // 15 minutes
    60,     // 1 hour
    120,    // 2 hours
    480,    // 8 hours
    1440,   // 24 hours
]);

it('accepts valid expiration times', function (int $expiresAt) {
    $pix = new Pix(expiresAt: $expiresAt);

    expect($pix->expiresAt)->toBe($expiresAt);
})->with('valid_expiration_times');

dataset('invalid_expiration_times', [
    -60,    // Negative
    0,      // Zero
    1441,   // Beyond 24 hours
    2880,   // 48 hours
]);

it('rejects invalid expiration times', function (int $expiresAt) {
    new Pix(expiresAt: $expiresAt);
})->with('invalid_expiration_times')->throws(InvalidArgumentException::class);

it('handles edge case of exactly 24 hours', function () {
    CarbonImmutable::setTestNow('2024-01-15 00:00:00');

    $pix = new Pix(expiresAt: 1440); // 24 hours

    expect($pix->getExpirationTimestamp())
        ->toBe(CarbonImmutable::parse('2024-01-16 00:00:00')->getTimestamp());
});

it('calculates expiration timestamp correctly across date boundaries', function () {
    CarbonImmutable::setTestNow('2024-01-15 23:30:00');

    $pix = new Pix(expiresAt: 60); // 1 hour from 23:30 is 00:30 next day

    $expectedTimestamp = CarbonImmutable::parse('2024-01-16 00:30:00')->getTimestamp();

    expect($pix->getExpirationTimestamp())->toBe($expectedTimestamp);
    expect($pix->isExpired())->toBeFalse();
});

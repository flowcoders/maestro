<?php

declare(strict_types=1);

use Flowcoders\Maestro\Enums\PhoneType;
use Flowcoders\Maestro\ValueObjects\Phone;

it('creates phone with valid E.164 number', function () {
    $phone = new Phone('+14155552671');

    expect($phone->number)->toBe('+14155552671');
    expect($phone->type)->toBeNull();
});

it('creates phone with type', function () {
    $phone = new Phone(
        number: '+5511987654321',
        type: PhoneType::MOBILE
    );

    expect($phone->number)->toBe('+5511987654321');
    expect($phone->type)->toBe(PhoneType::MOBILE);
});

it('accepts valid E.164 formats', function (string $phoneNumber) {
    $phone = new Phone($phoneNumber);

    expect($phone->number)->toBe($phoneNumber);
})->with([
    '+14155552671',      // US
    '+5511987654321',    // Brazil mobile
    '+551133334444',     // Brazil landline
    '+447700900123',     // UK
    '+33123456789',      // France
    '+4930123456789',    // Germany
    '+81901234567',      // Japan
    '+86138000000000',   // China
]);

it('throws exception for numbers not starting with plus', function () {
    new Phone('14155552671');
})->throws(InvalidArgumentException::class, 'Phone number must be in E.164 format (e.g., +14155552671).');

it('throws exception for numbers starting with +0', function () {
    new Phone('+04155552671');
})->throws(InvalidArgumentException::class, 'Phone number must be in E.164 format (e.g., +14155552671).');

it('throws exception for too short numbers', function () {
    new Phone('+1234567');
})->throws(InvalidArgumentException::class, 'Phone number must be in E.164 format (e.g., +14155552671).');

it('throws exception for too long numbers', function () {
    new Phone('+12345678901234567');
})->throws(InvalidArgumentException::class, 'Phone number must be in E.164 format (e.g., +14155552671).');

it('throws exception for numbers with non-digits after plus', function () {
    new Phone('+abc123456789');
})->throws(InvalidArgumentException::class, 'Phone number must be in E.164 format (e.g., +14155552671).');

it('throws exception for empty number', function () {
    new Phone('');
})->throws(InvalidArgumentException::class, 'Phone number must be in E.164 format (e.g., +14155552671).');

dataset('invalid_phone_numbers', [
    '14155552671',        // Missing +
    '+04155552671',       // Starts with 0
    '+1234567',          // Too short
    '+12345678901234567', // Too long
    '+abc123456789',     // Contains letters
    '',                  // Empty
    '+',                 // Just plus
    '1234567890',        // No plus, domestic format
    '+1 (415) 555-2671', // Contains spaces and formatting
]);

it('rejects invalid phone number formats', function (string $phoneNumber) {
    new Phone($phoneNumber);
})->with('invalid_phone_numbers')->throws(InvalidArgumentException::class);

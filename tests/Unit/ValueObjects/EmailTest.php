<?php

declare(strict_types=1);

use Flowcoders\Maestro\ValueObjects\Email;

it('creates email with valid address', function () {
    $email = new Email('user@example.com');

    expect($email->value)->toBe('user@example.com');
});

it('accepts various valid email formats', function (string $emailAddress) {
    $email = new Email($emailAddress);

    expect($email->value)->toBe($emailAddress);
})->with([
    'user@example.com',
    'test.email@domain.co.uk',
    'user+tag@example.org',
    'user_name@example-domain.com',
    'firstname.lastname@subdomain.example.com',
    '123@numbers.com',
]);

it('throws exception for invalid email format', function (string $invalidEmail) {
    new Email($invalidEmail);
})->with([
    'invalid-email',
    '@domain.com',
    'user@',
    'user..double.dot@example.com',
    'user@domain',
    '',
    ' ',
    'user@domain.',
    'user@.domain.com',
    'user@localhost',
])->throws(InvalidArgumentException::class);

it('throws exception with descriptive message for invalid email', function () {
    try {
        new Email('invalid-email');
    } catch (InvalidArgumentException $e) {
        expect($e->getMessage())->toBe('Invalid email format: invalid-email');
    }
});

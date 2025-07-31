<?php

declare(strict_types=1);

use Flowcoders\Maestro\Enums\CountryCode;
use Flowcoders\Maestro\ValueObjects\Address;

it('creates address with valid data', function () {
    $address = new Address(
        postalCode: '12345-678',
        streetLine1: 'Rua das Flores, 123',
        city: 'São Paulo',
        stateOrProvince: 'SP',
        countryCode: CountryCode::BR,
        streetLine2: 'Apt 45',
        neighborhood: 'Centro',
        complement: 'Próximo ao parque'
    );

    expect($address->postalCode)->toBe('12345-678');
    expect($address->streetLine1)->toBe('Rua das Flores, 123');
    expect($address->city)->toBe('São Paulo');
    expect($address->stateOrProvince)->toBe('SP');
    expect($address->countryCode)->toBe(CountryCode::BR);
    expect($address->streetLine2)->toBe('Apt 45');
    expect($address->neighborhood)->toBe('Centro');
    expect($address->complement)->toBe('Próximo ao parque');
});

it('creates address with only required fields', function () {
    $address = new Address(
        postalCode: '12345',
        streetLine1: 'Main Street',
        city: 'New York',
        stateOrProvince: 'NY',
        countryCode: CountryCode::US
    );

    expect($address->postalCode)->toBe('12345');
    expect($address->streetLine1)->toBe('Main Street');
    expect($address->city)->toBe('New York');
    expect($address->stateOrProvince)->toBe('NY');
    expect($address->countryCode)->toBe(CountryCode::US);
    expect($address->streetLine2)->toBeNull();
    expect($address->neighborhood)->toBeNull();
    expect($address->complement)->toBeNull();
});

it('throws exception for empty street line 1', function () {
    new Address(
        postalCode: '12345',
        streetLine1: '',
        city: 'São Paulo',
        stateOrProvince: 'SP',
        countryCode: CountryCode::BR
    );
})->throws(InvalidArgumentException::class, 'Street line 1 must be at least 2 characters.');

it('throws exception for short street line 1', function () {
    new Address(
        postalCode: '12345',
        streetLine1: 'A',
        city: 'São Paulo',
        stateOrProvince: 'SP',
        countryCode: CountryCode::BR
    );
})->throws(InvalidArgumentException::class, 'Street line 1 must be at least 2 characters.');

it('throws exception for empty city', function () {
    new Address(
        postalCode: '12345',
        streetLine1: 'Main Street',
        city: '',
        stateOrProvince: 'SP',
        countryCode: CountryCode::BR
    );
})->throws(InvalidArgumentException::class, 'City cannot be empty.');

it('throws exception for empty state or province', function () {
    new Address(
        postalCode: '12345',
        streetLine1: 'Main Street',
        city: 'São Paulo',
        stateOrProvince: '',
        countryCode: CountryCode::BR
    );
})->throws(InvalidArgumentException::class, 'State or province cannot be empty.');

it('throws exception for short postal code', function () {
    new Address(
        postalCode: '12',
        streetLine1: 'Main Street',
        city: 'São Paulo',
        stateOrProvince: 'SP',
        countryCode: CountryCode::BR
    );
})->throws(InvalidArgumentException::class, 'Postal code seems too short.');

it('allows empty postal code', function () {
    $address = new Address(
        postalCode: '',
        streetLine1: 'Main Street',
        city: 'São Paulo',
        stateOrProvince: 'SP',
        countryCode: CountryCode::BR
    );

    expect($address->postalCode)->toBe('');
});

it('formats address correctly with all fields', function () {
    $address = new Address(
        postalCode: '12345-678',
        streetLine1: 'Rua das Flores, 123',
        city: 'São Paulo',
        stateOrProvince: 'SP',
        countryCode: CountryCode::BR,
        streetLine2: 'Apt 45',
        neighborhood: 'Centro',
        complement: 'Próximo ao parque'
    );

    $formatted = $address->formatted();

    expect($formatted)->toBe('Rua das Flores, 123, Apt 45, São Paulo, SP, 12345-678, BR');
});

it('formats address correctly with minimal fields', function () {
    $address = new Address(
        postalCode: '12345',
        streetLine1: 'Main Street',
        city: 'New York',
        stateOrProvince: 'NY',
        countryCode: CountryCode::US
    );

    $formatted = $address->formatted();

    expect($formatted)->toBe('Main Street, New York, NY, 12345, US');
});

it('formats address without postal code', function () {
    $address = new Address(
        postalCode: '',
        streetLine1: 'Main Street',
        city: 'São Paulo',
        stateOrProvince: 'SP',
        countryCode: CountryCode::BR
    );

    $formatted = $address->formatted();

    expect($formatted)->toBe('Main Street, São Paulo, SP, BR');
});

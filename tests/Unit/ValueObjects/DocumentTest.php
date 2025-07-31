<?php

declare(strict_types=1);

use Flowcoders\Maestro\Enums\DocumentType;
use Flowcoders\Maestro\ValueObjects\Document;

it('creates document with valid CPF', function () {
    $document = new Document(
        type: DocumentType::CPF,
        value: '12345678909'
    );

    expect($document->type)->toBe(DocumentType::CPF);
    expect($document->value)->toBe('12345678909');
});

it('creates document with valid CNPJ', function () {
    $document = new Document(
        type: DocumentType::CNPJ,
        value: '12345678000195'
    );

    expect($document->type)->toBe(DocumentType::CNPJ);
    expect($document->value)->toBe('12345678000195');
});

it('creates document with passport', function () {
    $document = new Document(
        type: DocumentType::PASSPORT,
        value: 'AB123456'
    );

    expect($document->type)->toBe(DocumentType::PASSPORT);
    expect($document->value)->toBe('AB123456');
});

it('creates document with other type', function () {
    $document = new Document(
        type: DocumentType::OTHER,
        value: 'DOC123456'
    );

    expect($document->type)->toBe(DocumentType::OTHER);
    expect($document->value)->toBe('DOC123456');
});

it('throws exception for empty value', function () {
    new Document(
        type: DocumentType::CPF,
        value: ''
    );
})->throws(InvalidArgumentException::class, 'Document value cannot be empty.');

it('throws exception for invalid CPF length', function () {
    new Document(
        type: DocumentType::CPF,
        value: '123456789'
    );
})->throws(InvalidArgumentException::class, 'Invalid CPF: 123456789');

it('throws exception for invalid CNPJ length', function () {
    new Document(
        type: DocumentType::CNPJ,
        value: '12345678000'
    );
})->throws(InvalidArgumentException::class, 'Invalid CNPJ length');

it('throws exception for invalid CPF with all same digits', function () {
    new Document(
        type: DocumentType::CPF,
        value: '11111111111'
    );
})->throws(InvalidArgumentException::class, 'Invalid CPF: 11111111111');

it('throws exception for invalid CPF check digits', function () {
    new Document(
        type: DocumentType::CPF,
        value: '12345678901'
    );
})->throws(InvalidArgumentException::class, 'Invalid CPF: 12345678901');

it('accepts valid CPF with correct check digits', function () {
    // Valid CPF: 11144477735
    $document = new Document(
        type: DocumentType::CPF,
        value: '11144477735'
    );

    expect($document->value)->toBe('11144477735');
});

it('validates CPF with formatted input', function () {
    // Should normalize and validate CPF
    $document = new Document(
        type: DocumentType::CPF,
        value: '111.444.777-35'
    );

    expect($document->value)->toBe('111.444.777-35');
});

dataset('invalid_cpfs_same_digits', [
    '00000000000',
    '11111111111',
    '22222222222',
    '33333333333',
    '44444444444',
    '55555555555',
    '66666666666',
    '77777777777',
    '88888888888',
    '99999999999',
]);

it('rejects CPF with all same digits', function (string $cpf) {
    new Document(
        type: DocumentType::CPF,
        value: $cpf
    );
})->with('invalid_cpfs_same_digits')->throws(InvalidArgumentException::class);

dataset('invalid_cpfs', [
    '11144477736', // Valid is 11144477735, changed last digit
    '52998224726', // Valid is 52998224725, changed last digit
    '14434847821', // Valid is 14434847820, changed last digit
    '12345678901', // Known invalid CPF
    '98765432101', // Known invalid CPF
]);

it('rejects invalid CPF with incorrect check digits', function (string $cpf) {
    new Document(
        type: DocumentType::CPF,
        value: $cpf
    );
})->with('invalid_cpfs')->throws(InvalidArgumentException::class);

dataset('valid_cpfs', [
    '11144477735',
    '52998224725',
    '83001195070',
]);

it('accepts valid CPF numbers', function (string $cpf) {
    $document = new Document(
        type: DocumentType::CPF,
        value: $cpf
    );

    expect($document->value)->toBe($cpf);
})->with('valid_cpfs');

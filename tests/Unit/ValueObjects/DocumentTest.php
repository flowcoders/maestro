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
})->throws(InvalidArgumentException::class, 'Invalid CNPJ: 12345678000');

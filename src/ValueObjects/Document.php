<?php

namespace Flowcoders\Maestro\ValueObjects;

use Flowcoders\Maestro\Enums\DocumentType;
use InvalidArgumentException;

readonly class Document
{
    public function __construct(
        public DocumentType $type,
        public string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->value)) {
            throw new InvalidArgumentException('Document value cannot be empty.');
        }

        if ($this->type->isCpf() && strlen($this->value) !== 11) {
            throw new InvalidArgumentException('Invalid CPF length');
        }

        if ($this->type->isCnpj() && strlen($this->value) !== 14) {
            throw new InvalidArgumentException('Invalid CNPJ length');
        }

        if ($this->type->isCpf()) {
            $normalizedValue = preg_replace('/[^0-9]/', '', $this->value);

            if (! $this->isValidCpf($normalizedValue)) {
                throw new InvalidArgumentException("Invalid CPF: {$this->value}");
            }
        }
    }

    private function isValidCpf(string $cpf): bool
    {
        if (strlen($cpf) !== 11) {
            return false;
        }

        // Check for known invalid sequences
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Validate first check digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $cpf[9] !== $digit1) {
            return false;
        }

        // Validate second check digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += ((int) $cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        return (int) $cpf[10] === $digit2;
    }
}

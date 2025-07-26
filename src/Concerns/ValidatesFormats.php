<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Concerns;

use InvalidArgumentException;

trait ValidatesFormats
{
    protected function validateAmount(int $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Invalid amount: {$amount}");
        }
    }

    protected function validateInstallments(int $installments): void
    {
        if ($installments <= 0) {
            throw new InvalidArgumentException("Invalid installments: {$installments}");
        }
    }

    protected function validateEmail(?string $email): void
    {
        if ($email === null) {
            return;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$email}");
        }
    }

    protected function validatePhoneNumber(?string $phone): void
    {
        if ($phone === null) {
            return;
        }

        // Example: validate Brazilian phone format
        if (! preg_match('/^\+?55\s?\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/', $phone)) {
            throw new InvalidArgumentException("Invalid phone number format: {$phone}");
        }
    }

    protected function validateCpf(?string $cpf): void
    {
        if ($cpf === null) {
            return;
        }

        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            throw new InvalidArgumentException("Invalid CPF format: {$cpf}");
        }

        // Validate CPF checksum
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $firstDigit = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $cpf[9] !== $firstDigit) {
            throw new InvalidArgumentException("Invalid CPF checksum");
        }

        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $secondDigit = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $cpf[10] !== $secondDigit) {
            throw new InvalidArgumentException("Invalid CPF checksum");
        }
    }

    protected function validatePostalCode(string $postalCode): void
    {
        // Brazilian postal code format (12345-678 or 12345678)
        if (! preg_match('/^\d{5}-?\d{3}$/', $postalCode)) {
            throw new InvalidArgumentException("Invalid postal code format: {$postalCode}");
        }
    }

    protected function normalizePostalCode(string $postalCode): string
    {
        // Remove all non-digits, then add dash in correct position
        $digits = preg_replace('/[^0-9]/', '', $postalCode);
        
        if (strlen($digits) !== 8) {
            throw new InvalidArgumentException("Invalid postal code format: {$postalCode}");
        }
        
        return substr($digits, 0, 5) . '-' . substr($digits, 5);
    }
}

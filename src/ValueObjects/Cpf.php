<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use InvalidArgumentException;
use Stringable;

readonly class Cpf implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $normalized = $this->normalize($value);

        if (! $this->isValid($normalized)) {
            throw new InvalidArgumentException("Invalid CPF: {$value}");
        }
    }

    private function normalize(string $cpf): string
    {
        return preg_replace('/[^0-9]/', '', $cpf);
    }

    private function isValid(string $cpf): bool
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

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function formatted(): string
    {
        $normalized = $this->normalize($this->value);

        return sprintf(
            '%s.%s.%s-%s',
            substr($normalized, 0, 3),
            substr($normalized, 3, 3),
            substr($normalized, 6, 3),
            substr($normalized, 9, 2)
        );
    }

    public function equals(Cpf $other): bool
    {
        return $this->normalize($this->value) === $this->normalize($other->value);
    }
}

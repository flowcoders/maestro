<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use Flowcoders\Maestro\Enums\PhoneType;
use InvalidArgumentException;

readonly class Phone
{
    public string $number;

    public function __construct(
        string $inputNumber,
        public ?PhoneType $type = null,
    ) {
        $this->number = $this->formatNumber($inputNumber);
        $this->validate();
    }

    private function formatNumber(string $inputNumber): string
    {
        // Remove any existing formatting
        $cleaned = preg_replace('/[^0-9]/', '', $inputNumber);
        
        // Add + prefix if not present
        if (!str_starts_with($inputNumber, '+')) {
            return '+' . $cleaned;
        }
        
        return '+' . $cleaned;
    }

    private function validate(): void
    {
        // Basic E.164 check: starts with +, 8-15 digits
        if (!preg_match('/^\+[1-9]\d{7,14}$/', $this->number)) {
            throw new InvalidArgumentException('Phone number must contain 8-15 digits after country code.');
        }
    }
}

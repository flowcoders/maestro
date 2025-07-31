<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects;

use Flowcoders\Maestro\Enums\PhoneType;
use InvalidArgumentException;

readonly class Phone
{
    public function __construct(
        public string $number,
        public ?PhoneType $type = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        // Basic E.164 check: starts with +, 8-15 digits
        if (!preg_match('/^\+[1-9]\d{7,14}$/', $this->number)) {
            throw new InvalidArgumentException('Phone number must be in E.164 format (e.g., +14155552671).');
        }
    }
}

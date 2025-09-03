<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\ValueObjects\PaymentMethod;

use Carbon\Carbon;
use Flowcoders\Maestro\Contracts\PaymentMethodInterface;
use Flowcoders\Maestro\Enums\PaymentMethod;
use Flowcoders\Maestro\Utils\TimezoneHelper;
use InvalidArgumentException;

readonly class Pix implements PaymentMethodInterface
{
    public function __construct(
        public ?string $expiresAt = null,
        public ?string $qrCode = null,
        public ?string $qrCodeBase64 = null,
        public ?string $qrCodeUrl = null,
        public ?string $qrCodeImage = null,
        public ?string $qrCodeImageBase64 = null,
    ) {
        $this->validateExpiresAt();
    }

    public function getExpiresAt(): Carbon
    {
        if ($this->expiresAt === null) {
            // Default to 1 hour from now if not specified
            return TimezoneHelper::now()->addHour();
        }

        return TimezoneHelper::parse($this->expiresAt);
    }

    private function validateExpiresAt(): void
    {
        if ($this->expiresAt === null) {
            return; // Valid - will use default 1 hour
        }

        try {
            $expiresDate = TimezoneHelper::parse($this->expiresAt);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Invalid expiresAt format. Use ISO 8601 format (e.g., '2024-12-31T23:59:59Z' or '2024-12-31T23:59:59-03:00') or date format (e.g., '2024-12-31')");
        }

        $now = TimezoneHelper::now();
        
        // Check if input is date-only format (Y-m-d)
        $isDateOnly = preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->expiresAt);
        
        if ($isDateOnly) {
            // For date-only format, compare only dates (valid if today or future)
            $expiresDateOnly = $expiresDate->format('Y-m-d');
            $nowDateOnly = $now->format('Y-m-d');
            
            if ($expiresDateOnly < $nowDateOnly) {
                throw new InvalidArgumentException('PIX expiration date must be today or in the future');
            }
        } else {
            // For full datetime format, use precise timestamp comparison
            if ($expiresDate <= $now) {
                throw new InvalidArgumentException('PIX expiration date must be in the future');
            }
        }

        // PIX payments typically expire within 24 hours
        $maxExpirationTime = $now->addHours(24);
        if ($expiresDate > $maxExpirationTime) {
            throw new InvalidArgumentException('PIX expiration date cannot be more than 24 hours in the future');
        }
    }

    public function getType(): string
    {
        return PaymentMethod::PIX->value;
    }

    public function isDocumentRequired(): bool
    {
        return true;
    }

    public function isExpired(): bool
    {
        return $this->getExpiresAt() <= TimezoneHelper::now();
    }

    public function getExpirationTimestamp(): int
    {
        return $this->getExpiresAt()->getTimestamp();
    }
}

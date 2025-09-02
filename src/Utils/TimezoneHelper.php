<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Utils;

use Carbon\Carbon;

class TimezoneHelper
{
    private static ?string $timezone = null;

    /**
     * Set the timezone for the package
     */
    public static function setTimezone(?string $timezone): void
    {
        self::$timezone = $timezone;
    }

    /**
     * Get the configured timezone, falling back to Laravel app timezone or system default
     */
    public static function getTimezone(): string
    {
        // Use explicitly set timezone first
        if (self::$timezone !== null) {
            return self::$timezone;
        }

        // Try Laravel app timezone if available
        if (function_exists('config') && config('app.timezone')) {
            return config('app.timezone');
        }

        // Fallback to system default
        return date_default_timezone_get();
    }

    /**
     * Create a Carbon instance with the correct timezone
     */
    public static function now(): Carbon
    {
        return Carbon::now(self::getTimezone());
    }

    /**
     * Parse a date string with the correct timezone
     */
    public static function parse(string $date): Carbon
    {
        return Carbon::parse($date, self::getTimezone());
    }

    /**
     * Create a Carbon instance from DateTime with the correct timezone
     */
    public static function fromDateTime(\DateTime $dateTime): Carbon
    {
        return Carbon::instance($dateTime)->setTimezone(self::getTimezone());
    }

    /**
     * Check if we're running in a Laravel environment
     */
    public static function isLaravelEnvironment(): bool
    {
        return function_exists('app') && function_exists('config');
    }
}

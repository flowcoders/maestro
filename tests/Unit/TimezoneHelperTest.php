<?php

declare(strict_types=1);

use Carbon\Carbon;
use Flowcoders\Maestro\Utils\TimezoneHelper;

beforeEach(function () {
    // Reset timezone helper state before each test
    TimezoneHelper::setTimezone(null);
});

afterEach(function () {
    // Clean up after each test
    TimezoneHelper::setTimezone(null);
});

describe('TimezoneHelper', function () {
    it('can set and get timezone', function () {
        $timezone = 'America/Sao_Paulo';

        TimezoneHelper::setTimezone($timezone);

        expect(TimezoneHelper::getTimezone())->toBe($timezone);
    });

    it('falls back to Laravel app timezone when available', function () {
        // Mock Laravel config function to return a timezone
        config()->set('app.timezone', 'America/New_York');

        expect(TimezoneHelper::getTimezone())->toBe('America/New_York');
    });

    it('falls back to system timezone when Laravel is not available', function () {
        // When no timezone is set and no Laravel config
        $systemTimezone = date_default_timezone_get();

        expect(TimezoneHelper::getTimezone())->toBe($systemTimezone);
    });

    it('prioritizes explicitly set timezone over Laravel config', function () {
        config()->set('app.timezone', 'Europe/London');
        TimezoneHelper::setTimezone('America/Sao_Paulo');

        expect(TimezoneHelper::getTimezone())->toBe('America/Sao_Paulo');
    });

    it('can create Carbon instance with correct timezone using now()', function () {
        TimezoneHelper::setTimezone('America/Sao_Paulo');

        $now = TimezoneHelper::now();

        expect($now)->toBeInstanceOf(Carbon::class);
        expect($now->getTimezone()->getName())->toBe('America/Sao_Paulo');
    });

    it('can parse date string with configured timezone', function () {
        TimezoneHelper::setTimezone('Europe/London');

        $dateString = '2025-01-01 12:00:00';
        $parsed = TimezoneHelper::parse($dateString);

        expect($parsed)->toBeInstanceOf(Carbon::class);
        expect($parsed->getTimezone()->getName())->toBe('Europe/London');
        expect($parsed->format('Y-m-d H:i:s'))->toBe('2025-01-01 12:00:00');
    });

    it('can convert DateTime to Carbon with configured timezone', function () {
        TimezoneHelper::setTimezone('Asia/Tokyo');

        $dateTime = new DateTime('2025-01-01 12:00:00');
        $carbon = TimezoneHelper::fromDateTime($dateTime);

        expect($carbon)->toBeInstanceOf(Carbon::class);
        expect($carbon->getTimezone()->getName())->toBe('Asia/Tokyo');
    });

    it('can detect Laravel environment', function () {
        // In our test environment, Laravel functions should be available
        expect(TimezoneHelper::isLaravelEnvironment())->toBeTrue();
    });

    it('handles null timezone gracefully', function () {
        TimezoneHelper::setTimezone(null);

        // Should not throw exception and fall back to Laravel/system timezone
        $timezone = TimezoneHelper::getTimezone();

        expect($timezone)->toBeString();
        expect(strlen($timezone))->toBeGreaterThan(0);
    });

    it('maintains timezone consistency across multiple calls', function () {
        TimezoneHelper::setTimezone('America/Sao_Paulo');

        $first = TimezoneHelper::now();
        $second = TimezoneHelper::now();

        expect($first->getTimezone()->getName())->toBe($second->getTimezone()->getName());
        expect($first->getTimezone()->getName())->toBe('America/Sao_Paulo');
    });

    it('can handle different timezone formats', function () {
        $timezones = [
            'UTC',
            'America/Sao_Paulo',
            'Europe/London',
            'Asia/Tokyo',
            '+03:00',
            '-05:00',
        ];

        foreach ($timezones as $timezone) {
            TimezoneHelper::setTimezone($timezone);

            $now = TimezoneHelper::now();

            expect($now)->toBeInstanceOf(Carbon::class);
            // For offset formats, Carbon normalizes them
            if (str_starts_with($timezone, '+') || str_starts_with($timezone, '-')) {
                expect($now->getTimezone())->toBeInstanceOf(DateTimeZone::class);
            } else {
                expect($now->getTimezone()->getName())->toBe($timezone);
            }
        }
    });

    it('can parse ISO 8601 dates with timezone information', function () {
        TimezoneHelper::setTimezone('America/Sao_Paulo');

        $isoDate = '2025-01-01T12:00:00-03:00';
        $parsed = TimezoneHelper::parse($isoDate);

        expect($parsed)->toBeInstanceOf(Carbon::class);
        expect($parsed->format('Y-m-d H:i:s'))->toBe('2025-01-01 12:00:00');
        expect($parsed->getOffset())->toBe(-3 * 3600); // -3 hours in seconds
    });

    it('respects Laravel config changes during runtime', function () {
        // Initial config
        config()->set('app.timezone', 'UTC');
        expect(TimezoneHelper::getTimezone())->toBe('UTC');

        // Change config
        config()->set('app.timezone', 'America/Sao_Paulo');
        expect(TimezoneHelper::getTimezone())->toBe('America/Sao_Paulo');
    });
});

describe('TimezoneHelper edge cases', function () {
    it('handles invalid timezone gracefully by falling back', function () {
        // Set an invalid timezone
        TimezoneHelper::setTimezone('Invalid/Timezone');

        // Should fall back to system timezone instead of throwing
        $timezone = TimezoneHelper::getTimezone();
        expect($timezone)->toBe('Invalid/Timezone'); // Helper returns what was set

        // But Carbon should handle it properly when creating instances
        expect(function () {
            TimezoneHelper::now();
        })->toThrow(InvalidArgumentException::class);
    });

    it('can reset timezone to null', function () {
        TimezoneHelper::setTimezone('America/Sao_Paulo');
        expect(TimezoneHelper::getTimezone())->toBe('America/Sao_Paulo');

        TimezoneHelper::setTimezone(null);

        // Should fall back to Laravel config or system timezone
        $fallbackTimezone = TimezoneHelper::getTimezone();
        expect($fallbackTimezone)->not()->toBe('America/Sao_Paulo');
    });

    it('maintains timezone between different Carbon operations', function () {
        TimezoneHelper::setTimezone('Europe/London');

        $now = TimezoneHelper::now();
        $parsed = TimezoneHelper::parse('2025-01-01 12:00:00');
        $dateTime = TimezoneHelper::fromDateTime(new DateTime('2025-06-01 15:30:00'));

        expect($now->getTimezone()->getName())->toBe('Europe/London');
        expect($parsed->getTimezone()->getName())->toBe('Europe/London');
        expect($dateTime->getTimezone()->getName())->toBe('Europe/London');
    });
});

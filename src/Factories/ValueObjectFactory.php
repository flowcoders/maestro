<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Factories;

use Flowcoders\Maestro\Enums\Country;
use Flowcoders\Maestro\ValueObjects\Cpf;
use Flowcoders\Maestro\ValueObjects\Email;
use Flowcoders\Maestro\ValueObjects\PhoneNumber;
use Flowcoders\Maestro\ValueObjects\PostalCode;

class ValueObjectFactory
{
    public static function createEmail(?string $email): ?Email
    {
        return $email !== null ? new Email($email) : null;
    }

    public static function createPhoneNumber(?string $phone): ?PhoneNumber
    {
        return $phone !== null ? new PhoneNumber($phone) : null;
    }

    public static function createCpf(?string $cpf): ?Cpf
    {
        return $cpf !== null ? new Cpf($cpf) : null;
    }

    public static function createPostalCode(?string $code, Country $country = Country::BR): ?PostalCode
    {
        return $code !== null ? new PostalCode($code, $country) : null;
    }
}

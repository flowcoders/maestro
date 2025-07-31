<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\DTOs;

use Flowcoders\Maestro\ValueObjects\Address;
use Flowcoders\Maestro\ValueObjects\Document;
use Flowcoders\Maestro\ValueObjects\Email;
use Flowcoders\Maestro\ValueObjects\Phone;

readonly class Customer
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public Email $email,
        public ?string $id = null,
        public ?Document $document = null,
        public ?Phone $phone = null,
        public ?Address $address = null,
    ) {
    }
}

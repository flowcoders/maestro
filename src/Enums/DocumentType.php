<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum DocumentType: string
{
    case CPF = 'CPF';
    case CNPJ = 'CNPJ';
    case PASSPORT = 'PASSPORT';
    case OTHER = 'OTHER';

    public function isCpf(): bool
    {
        return $this === self::CPF;
    }

    public function isCnpj(): bool
    {
        return $this === self::CNPJ;
    }

    public function isPassport(): bool
    {
        return $this === self::PASSPORT;
    }

    public function isOther(): bool
    {
        return $this === self::OTHER;
    }
}

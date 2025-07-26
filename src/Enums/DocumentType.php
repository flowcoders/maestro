<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum DocumentType: string
{
    case CPF = 'CPF';
    case CNPJ = 'CNPJ';
}

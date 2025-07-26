<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum Currency: string
{
    case BRL = 'BRL';
    case USD = 'USD';
    case EUR = 'EUR';
    case ARS = 'ARS';
    case MXN = 'MXN';
    case COP = 'COP';
    case CLP = 'CLP';
    case PEN = 'PEN';
    case UYU = 'UYU';
}

<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum CardBrand: string
{
    case VISA = 'VISA';
    case MASTER = 'MASTER';
    case AMEX = 'AMEX';
    case ELO = 'ELO';
    case DINERS = 'DINERS';
    case DISCOVER = 'DISCOVER';
    case JCB = 'JCB';
    case MAESTRO = 'MAESTRO';
    case UNIONPAY = 'UNIONPAY';
}

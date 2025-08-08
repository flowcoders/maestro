<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum CardBrand: string
{
    case AMEX = 'AMEX';
    case AURA = 'AURA';
    case DINERS = 'DINERS';
    case DISCOVER = 'DISCOVER';
    case ELO = 'ELO';
    case JCB = 'JCB';
    case HIPERCARD = 'HIPERCARD';
    case MAESTRO = 'MAESTRO';
    case MASTER = 'MASTER';
    case UNIONPAY = 'UNIONPAY';
    case VISA = 'VISA';
}

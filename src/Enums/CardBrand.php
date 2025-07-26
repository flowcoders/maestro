<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum CardBrand: string
{
    case VISA = 'visa';
    case MASTERCARD = 'mastercard';
    case AMERICAN_EXPRESS = 'amex';
    case ELO = 'elo';
    case HIPERCARD = 'hipercard';
    case DINERS = 'diners';
    case DISCOVER = 'discover';
    case JCB = 'jcb';
    case MAESTRO = 'maestro';
    case UNIONPAY = 'unionpay';
}

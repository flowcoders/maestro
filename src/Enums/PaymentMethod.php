<?php

declare(strict_types=1);

namespace Flowcoders\Maestro\Enums;

enum PaymentMethod: string
{
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case PIX = 'pix';
}

<?php

namespace App\Enums;

Enum PaymentTransactionStatus:string
{
    case adding = 'adding';
    case withdraw = 'withdraw';
    case awaiting = 'awaiting';
    case failed = 'failed';
    case expired = 'expired';
}
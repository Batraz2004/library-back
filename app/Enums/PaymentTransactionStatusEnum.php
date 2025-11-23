<?php

namespace App\Enums;

Enum PaymentTransactionStatusEnum:string
{
    case adding = 'adding';
    case withdraw = 'withdraw';
    case awaiting = 'awaiting';
    case failed = 'failed';
    case expired = 'expired';
}
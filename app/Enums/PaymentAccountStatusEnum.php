<?php

namespace App\Enums;

Enum PaymentAccountStatusEnum:string
{
    case active = 'active';
    case awaiting = 'awaiting';
    case expired = 'expired';
    case failed = 'failed';
    case disabled = 'disabled';
}
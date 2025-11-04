<?php

namespace App\Enums;

Enum AccountStatusEnum:string
{
    case active = 'active';
    case awaiting = 'awaiting';
    case expired = 'expired';
    case failed = 'failed';
}
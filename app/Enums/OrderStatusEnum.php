<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case active = 'active';
    case cancelled = 'cancelled';
    case error = 'error';
    case disabled = 'disabled';
}

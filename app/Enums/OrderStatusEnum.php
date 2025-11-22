<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case active = 'active';
    case completed = 'completed';
    case cancelled = 'cancelled';
    case disabled = 'disabled';
    case error = 'error';
}

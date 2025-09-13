<?php

namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

Enum RoleEnum: string
{
    case ADMIN = 'admin';
    case SELLER = 'seller';
    case USER = 'user';

    public function label(): string
    {
        return match($this)
        {
            self::ADMIN => 'Админ',
            self::SELLER => 'Продавец',
            self::USER => 'Пользователь',
        };
    }
}
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Admin extends Authenticatable implements FilamentUser
{
    //
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}

<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Contracts\Role;

class RoleseAndPermissions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = app(Role::class)->findOrCreate(RoleEnum::ADMIN->value, 'admin');
        $sellerRole = app(Role::class)->findOrCreate(RoleEnum::SELLER->value, 'seller');
        $userRole = app(Role::class)->findOrCreate(RoleEnum::USER->value, 'web');
    }
}

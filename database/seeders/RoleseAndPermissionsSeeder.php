<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleseAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = app(Role::class)->findOrCreate(RoleEnum::ADMIN->value, RoleEnum::ADMIN->value);
        $sellerRole = app(Role::class)->findOrCreate(RoleEnum::SELLER->value, RoleEnum::SELLER->value);
        $userRole = app(Role::class)->findOrCreate(RoleEnum::USER->value, 'api');

        $permissions = [
            'view Post',
            'viewAny Post',
            'update Post',
            'create Post',
            'delete Post',
        ];
        foreach($permissions as $key => $val)
        {
            $permission = app(Permission::class)->findOrCreate($val, RoleEnum::ADMIN->value);
            $adminRole->givePermissionTo($permission);
        }
        // $adminRole->hasDirectPermission('view Post');

    }
}

<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createAdminUser();
    }

    private function createAdminUser()
    {
        $fields = [
            'name' => 'admin',
            'email' => 'admin@admin.dev',
            'phone' => null,
            'password' => Hash::make('dev'),
        ];

        $admin = Admin::create([
            ...$fields
        ]);

        $role = app(Role::class)->findOrCreate(RoleEnum::ADMIN->value, 'admin');
        $admin->assignRole(RoleEnum::ADMIN->value);

        // $role = Role::create(['name' => RoleEnum::ADMIN->value]);

        // $admin->assignRole(RoleEnum::ADMIN->value);

    }
}

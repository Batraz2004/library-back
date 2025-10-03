<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'email' => 'admin@lib.dev',
            'password' => Hash::make('dev'),
        ];

        $admin = User::create([
            ...$fields
        ]);

        // $role = app(Role::class)->findOrCreate(RoleEnum::ADMIN->value, 'admin'); или // $role = Role::create(['name' => RoleEnum::ADMIN->value]);
        // $admin->assignRole(RoleEnum::ADMIN->value);
    }
}

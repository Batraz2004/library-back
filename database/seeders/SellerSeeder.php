<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Admin;
use App\Models\Seller;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SellerSeeder extends Seeder
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
            'email' => 'seller@lib.dev',
            'phone' => null,
            'password' => Hash::make('dev'),
        ];

        $admin = Seller::create([
            ...$fields
        ]);

        // $role = app(Role::class)->findOrCreate(RoleEnum::ADMIN->value, 'admin'); или // $role = Role::create(['name' => RoleEnum::ADMIN->value]);
        $admin->assignRole(RoleEnum::SELLER->value);
    }

}

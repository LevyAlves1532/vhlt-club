<?php

namespace Database\Seeders;

use App\Enum\PermissionsEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Lêvy',
            'email' => 'levy.pereiraa1532@gmail.com',
            'password' => 'admin123',
            'permission' => PermissionsEnum::SUPER_ADMIN,
        ]);

        User::create([
            'name' => 'Daniel Cauan',
            'email' => 'danielcauanpa299@gmail.com',
            'password' => 'admin123',
            'permission' => PermissionsEnum::ADMIN,
        ]);

        User::create([
            'name' => 'Josue',
            'email' => 'josue@gmail.com',
            'password' => 'admin123',
            'permission' => PermissionsEnum::COMMUNITY_MANAGER,
        ]);
    }
}

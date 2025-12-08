<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'ionut.pirlogea@yahoo.com'],
            [
                'name' => '10NUT',
                'password' => bcrypt('Wasd2012!@'),
                'is_admin' => 1,
            ]
        );
    }
}

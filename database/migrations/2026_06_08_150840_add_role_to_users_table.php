<?php

namespace database\seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Lucas Monteiro',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'gestor@gmail.com'],
            [
                'name' => 'Pedro Nogueira',
                'password' => Hash::make('12345678'),
                'role' => 'gestor',
            ]
        );
    }
}

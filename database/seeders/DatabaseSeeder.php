<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Cria o usuário de acesso ao sistema e popula o cardápio.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('OWNER_EMAIL', 'proprietaria@restaurante.test')],
            [
                'name' => env('OWNER_NAME', 'Proprietária'),
                'password' => Hash::make(env('OWNER_PASSWORD', 'senha1234')),
                'email_verified_at' => now(),
            ]
        );

        $this->call(MenuSeeder::class);
    }
}

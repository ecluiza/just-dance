<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Usuário do painel /admin. Troque a senha no .env (ADMIN_PASSWORD) antes de publicar.
        User::query()->firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@justdance.local')],
            ['name' => 'Admin', 'password' => env('ADMIN_PASSWORD', 'troque-esta-senha')],
        );

        $this->call(JogoSeeder::class);
    }
}

<?php

namespace Database\Seeders;

use App\Database\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            [
                'email' => 'admin@inventaire.test',
            ],
            User::factory()
                ->admin()
                ->make([
                    'name' => 'Admin Démo',
                    'email' => 'admin@inventaire.test',
                ])
                ->toArray()
        );

        User::firstOrCreate(
            [
                'email' => 'moderation@inventaire.test',
            ],
            User::factory()
                ->admin()
                ->make([
                    'name' => 'Équipe Modération',
                    'email' => 'moderation@inventaire.test',
                ])
                ->toArray()
        );

        User::firstOrCreate(
            [
                'email' => 'support@inventaire.test',
            ],
            User::factory()
                ->admin()
                ->make([
                    'name' => 'Support Démo',
                    'email' => 'support@inventaire.test',
                ])
                ->toArray()
        );

        User::factory()
            ->admin()
            ->count(10)
            ->create();

        $this->command->info('Admin users seeded.');
    }
}

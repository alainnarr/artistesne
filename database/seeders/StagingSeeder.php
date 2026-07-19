<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StagingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsersSeeder::class,
            RegistrationsSeeder::class,
            ArtistChangeRequestsSeeder::class,
            //DemoSeeder::class,
        ]);
    }
}

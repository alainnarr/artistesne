<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class V1CoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DisciplinesSeeder::class,
            ActivitiesSeeder::class,
        ]);
    }
}

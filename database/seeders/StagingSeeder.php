<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StagingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TaxonomyTermsSeeder::class,
            DemoSeeder::class,
        ]);
    }
}

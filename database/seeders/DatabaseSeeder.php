<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Call your individual seeders
        $this->call([
            UserSeeder::class,
                   RelativesTableSeeder::class,
            InterestsTableSeeder::class,
            ProjectSeeder::class,
            TimesheetSeeder::class,
            AttributeSeeder::class,
            AttributeValueSeeder::class,
                 RelativesTableSeeder::class,
            InterestsTableSeeder::class,
        ]);
    }
}

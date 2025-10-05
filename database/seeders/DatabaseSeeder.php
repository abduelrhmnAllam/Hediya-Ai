<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Call your individual seeders
        $this->call([
            ProductsTableSeeder::class,
            UserSeeder::class,
             RelativesTableSeeder::class,
             InterestsTableSeeder::class,
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            ProjectSeeder::class,
            TimesheetSeeder::class,

        ]);
    }
}

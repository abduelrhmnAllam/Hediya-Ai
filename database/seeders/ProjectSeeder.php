<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Projects;

class ProjectSeeder extends Seeder
{
    public function run()
    {
        // Create some projects with basic fields
        Projects::factory()->count(5)->create();
    }
}

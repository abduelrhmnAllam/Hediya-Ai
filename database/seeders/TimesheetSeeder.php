<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Timesheets;

class TimesheetSeeder extends Seeder
{
    public function run()
    {
        // Create timesheet records for existing users and projects.
        Timesheets::factory()->count(10)->create();
    }
}

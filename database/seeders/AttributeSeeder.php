<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attributes;

class AttributeSeeder extends Seeder
{
    public function run()
    {
        // Create some sample attribute definitions
        Attributes::firstOrCreate(['name' => 'department'], ['type' => 'text']);
        Attributes::firstOrCreate(['name' => 'start_date'], ['type' => 'date']);
        Attributes::firstOrCreate(['name' => 'end_date'], ['type' => 'date']);
        Attributes::firstOrCreate(['name' => 'budget'], ['type' => 'number']);
    }
}

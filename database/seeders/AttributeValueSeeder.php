<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttributeValues;
use App\Models\Projects;
use App\Models\Attributes;

class AttributeValueSeeder extends Seeder
{
    public function run()
    {
        // For each project, assign some random attribute values
        $projects = Projects::all();
        $attributes = Attributes::all();

        foreach ($projects as $project) {
            foreach ($attributes as $attribute) {
                AttributeValues::create([
                    'attribute_id' => $attribute->id,
                    'entity_id' => $project->id,
                    'value' => $this->generateValue($attribute->name),
                ]);
            }
        }
    }

    private function generateValue($attributeName)
    {
        switch ($attributeName) {
            case 'department':
                return collect(['IT', 'Finance', 'HR', 'Marketing'])->random();
            case 'start_date':
                return now()->subDays(rand(1, 100))->toDateString();
            case 'end_date':
                return now()->addDays(rand(1, 100))->toDateString();
            case 'budget':
                return rand(5000, 50000);
            default:
                return '';
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OccasionName;

class OccasionNamesTableSeeder extends Seeder
{
    public function run(): void
    {
        $occasions = [
            [
                'name' => 'Birthday',
                'type' => 'birthday',
                'description' => 'Celebration of birth date.',
            ],
            [
                'name' => 'Graduation',
                'type' => 'graduation',
                'description' => 'Celebration of finishing school or university.',
            ],
            [
                'name' => 'Wedding',
                'type' => 'wedding',
                'description' => 'Celebration of marriage ceremony.',
            ],
            [
                'name' => 'Engagement',
                'type' => 'engagement',
                'description' => 'Celebration of engagement or proposal.',
            ],
            [
                'name' => 'Anniversary',
                'type' => 'anniversary',
                'description' => 'Celebration of relationship or marriage anniversary.',
            ],
            [
                'name' => 'New Baby / Baby Shower',
                'type' => 'new_baby',
                'description' => 'Celebration for welcoming a new baby.',
            ],
            [
                'name' => 'Housewarming',
                'type' => 'housewarming',
                'description' => 'Celebration for moving into a new home.',
            ],
            [
                'name' => 'Promotion / New Job',
                'type' => 'promotion',
                'description' => 'Celebration for job promotion or new position.',
            ],
            [
                'name' => 'Other',
                'type' => 'other',
                'description' => 'Custom or undefined occasion type.',
            ],
        ];

        foreach ($occasions as $occasion) {
            OccasionName::firstOrCreate(
                ['type' => $occasion['type']],
                $occasion
            );
        }
    }
}

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
                'background_color' => '#C42424',
                'image_background' => 'uploads/occasions/birthday.png',
            ],
            [
                'name' => 'Graduation',
                'type' => 'graduation',
                'description' => 'Celebration of finishing school or university.',
                'background_color' => '#1F9854',
                'image_background' => 'uploads/occasions/graduation.png',
            ],
            [
                'name' => 'Wedding',
                'type' => 'wedding',
                'description' => 'Celebration of marriage ceremony.',
                'background_color' => '#X9B900',
                'image_background' => 'uploads/occasions/wedding.png',
            ],
            [
                'name' => 'Engagement',
                'type' => 'engagement',
                'description' => 'Celebration of engagement or proposal.',
                'background_color' => '#C42424',
                'image_background' => 'uploads/occasions/engagement.png',
            ],
            [
                'name' => 'Anniversary',
                'type' => 'anniversary',
                'description' => 'Celebration of relationship or marriage anniversary.',
                'background_color' => '#X9B900',
                'image_background' => 'uploads/occasions/anniversary.png',
            ],
            [
                'name' => 'New Baby / Baby Shower',
                'type' => 'new_baby',
                'description' => 'Celebration for welcoming a new baby.',
                'background_color' => '#1F9854',
                'image_background' => 'uploads/occasions/new_baby.png',
            ],
            [
                'name' => 'Housewarming',
                'type' => 'housewarming',
                'description' => 'Celebration for moving into a new home.',
                'background_color' => '#C42424',
                'image_background' => 'uploads/occasions/housewarming.png',
            ],
            [
                'name' => 'Promotion / New Job',
                'type' => 'promotion',
                'description' => 'Celebration for job promotion or new position.',
                'background_color' => '#X9B900',
                'image_background' => 'uploads/occasions/promotion.png',
            ],
            [
                'name' => 'Other',
                'type' => 'other',
                'description' => 'Custom or undefined occasion type.',
                'background_color' => '#1F9854',
                'image_background' => 'uploads/occasions/other.png',
            ],
        ];

        foreach ($occasions as $o) {
            OccasionName::updateOrCreate(
                ['type' => $o['type']], // unique key
                $o
            );
        }
    }
}

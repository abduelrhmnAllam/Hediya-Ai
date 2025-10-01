<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterestsTableSeeder extends Seeder
{
    public function run(): void
    {
        $interests = [
            'Sports',
            'Music',
            'Reading',
            'Travel',
            'Cooking',
            'Video Games',
            'Photography',
            'Movies',
            'Technology',
            'Shopping',
            'Art',
            'Writing',
            'Fitness',
            'Hiking',
            'Cycling',
            'Swimming',
            'Dancing',
            'Fashion',
            'Gardening',
            'Volunteering',
            'History',
            'Science',
            'DIY Crafts',
            'Meditation',
            'Yoga',
            'Programming',
            'Blogging',
            'Collecting',
            'Pets & Animals',
            'Cars',
            'Motorcycles',
            'Business',
            'Investing',
            'Food Tasting',
            'Languages',
            'Culture',
            'Theater',
            'Comics',
            'Podcasts',
        ];

        foreach ($interests as $title) {
            DB::table('interests')->insert([
                'title' => $title,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

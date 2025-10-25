<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterestsTableSeeder extends Seeder
{
    public function run(): void
    {
         DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 🧹 إفراغ الجداول المرتبطة
        DB::table('person_interest')->truncate();
        DB::table('people')->truncate();
   
            DB::table('interests')->truncate();
        $interests = [
            ['title' => 'Sports',        'icon' => 'interests/sports.png'],
            ['title' => 'Music',         'icon' => 'interests/music.png'],
            ['title' => 'Reading',       'icon' => 'interests/reading.png'],
            ['title' => 'Travel',        'icon' => 'interests/travel.png'],
            ['title' => 'Cooking',       'icon' => 'interests/cooking.png'],
            ['title' => 'Video Games',   'icon' => 'interests/gaming.png'],
            ['title' => 'Photography',   'icon' => 'interests/photography.png'],
            ['title' => 'Movies',        'icon' => 'interests/movies.png'],
            ['title' => 'Technology',    'icon' => 'interests/technology.png'],
            ['title' => 'Shopping',      'icon' => 'interests/shopping.png'],
            ['title' => 'Art',           'icon' => 'interests/art.png'],
            ['title' => 'Writing',       'icon' => 'interests/writing.png'],
            ['title' => 'Fitness',       'icon' => 'interests/fitness.png'],
            ['title' => 'Hiking',        'icon' => 'interests/hiking.png'],
            ['title' => 'Cycling',       'icon' => 'interests/cycling.png'],
            ['title' => 'Swimming',      'icon' => 'interests/swimming.png'],
            ['title' => 'Dancing',       'icon' => 'interests/dancing.png'],
            ['title' => 'Fashion',       'icon' => 'interests/fashion.png'],
            ['title' => 'Gardening',     'icon' => 'interests/gardening.png'],
            ['title' => 'Volunteering',  'icon' => 'interests/volunteering.png'],
        ];

        foreach ($interests as $interest) {
            DB::table('interests')->insert([
                'title'      => $interest['title'],
                'icon'       => $interest['icon'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

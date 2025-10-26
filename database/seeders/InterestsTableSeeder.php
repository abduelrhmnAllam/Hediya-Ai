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
            ['title' => 'Sports',        'icon' => 'interests/1.png'],
            ['title' => 'Music',         'icon' => 'interests/2.png'],
            ['title' => 'Reading',       'icon' => 'interests/3.png'],
            ['title' => 'Travel',        'icon' => 'interests/8.png'],
            ['title' => 'Cooking',       'icon' => 'interests/2.png'],
            ['title' => 'Video Games',   'icon' => 'interests/3.png'],
            ['title' => 'Photography',   'icon' => 'interests/4.png'],
            ['title' => 'Movies',        'icon' => 'interests/8.png'],
            ['title' => 'Technology',    'icon' => 'interests/4.png'],
            ['title' => 'Shopping',      'icon' => 'interests/shopping.png'],
            ['title' => 'Art',           'icon' => 'interests/1.png'],
            ['title' => 'Writing',       'icon' => 'interests/9.png'],
            ['title' => 'Fitness',       'icon' => 'interests/10.png'],
            ['title' => 'Hiking',        'icon' => 'interests/7.png'],
            ['title' => 'Cycling',       'icon' => 'interests/3.png'],
            ['title' => 'Swimming',      'icon' => 'interests/4.png'],
            ['title' => 'Dancing',       'icon' => 'interests/6.png'],
            ['title' => 'Fashion',       'icon' => 'interests/7.png'],
            ['title' => 'Gardening',     'icon' => 'interests/4.png'],
            ['title' => 'Volunteering',  'icon' => 'interests/6.png'],
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

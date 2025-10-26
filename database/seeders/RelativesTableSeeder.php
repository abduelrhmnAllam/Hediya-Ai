<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RelativesTableSeeder extends Seeder
{
    public function run(): void
    {
 DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 🧹 إفراغ الجداول المرتبطة
        DB::table('person_interest')->truncate();
        DB::table('people')->truncate();
         DB::table('relatives')->truncate();

        $relatives = [
            ['title' => 'Father',        'image' => 'relatives/1.png'],
            ['title' => 'Mother',        'image' => 'relatives/2.png'],
            ['title' => 'Brother',       'image' => 'relatives/3.png'],
            ['title' => 'Sister',        'image' => 'relatives/5.png'],
            ['title' => 'Grandfather',   'image' => 'relatives/6.png'],
            ['title' => 'Grandmother',   'image' => 'relatives/5.png'],
            ['title' => 'Son',           'image' => 'relatives/3.png'],
            ['title' => 'Daughter',      'image' => 'relatives/5.png'],
            ['title' => 'Uncle',         'image' => 'relatives/6.png'],
            ['title' => 'Aunt',          'image' => 'relatives/4.png'],
            ['title' => 'Friend',        'image' => 'relatives/3.png'],
            ['title' => 'Wife',          'image' => 'relatives/2.png'],
            ['title' => 'Husband',       'image' => 'relatives/6.png'],
            ['title' => 'Neighbor',      'image' => 'relatives/7.png'],
            ['title' => 'Partner',       'image' => 'relatives/4.png'],
        ];

        foreach ($relatives as $relative) {
            DB::table('relatives')->insert([
                'title'      => $relative['title'],
                'image'      => $relative['image'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

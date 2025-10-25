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
            ['title' => 'Father',        'image' => 'relatives/father.png'],
            ['title' => 'Mother',        'image' => 'relatives/mother.png'],
            ['title' => 'Brother',       'image' => 'relatives/brother.png'],
            ['title' => 'Sister',        'image' => 'relatives/sister.png'],
            ['title' => 'Grandfather',   'image' => 'relatives/grandfather.png'],
            ['title' => 'Grandmother',   'image' => 'relatives/grandmother.png'],
            ['title' => 'Son',           'image' => 'relatives/son.png'],
            ['title' => 'Daughter',      'image' => 'relatives/daughter.png'],
            ['title' => 'Uncle',         'image' => 'relatives/uncle.png'],
            ['title' => 'Aunt',          'image' => 'relatives/aunt.png'],
            ['title' => 'Friend',        'image' => 'relatives/friend.png'],
            ['title' => 'Wife',          'image' => 'relatives/wife.png'],
            ['title' => 'Husband',       'image' => 'relatives/husband.png'],
            ['title' => 'Neighbor',      'image' => 'relatives/neighbor.png'],
            ['title' => 'Partner',       'image' => 'relatives/partner.png'],
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

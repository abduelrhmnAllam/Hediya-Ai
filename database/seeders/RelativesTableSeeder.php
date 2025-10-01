<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RelativesTableSeeder extends Seeder
{
    public function run(): void
    {
        $relatives = [
            'Father',
            'Mother',
            'Brother',
            'Sister',
            'Grandfather',
            'Grandmother',
            'Son',
            'Daughter',
            'Uncle',
            'Aunt',
            'Cousin',
            'Nephew',
            'Niece',
            'Husband',
            'Wife',
            'Friend',
            'Colleague',
            'Neighbor',
            'Guardian',
            'Partner',
        ];

        foreach ($relatives as $title) {
            DB::table('relatives')->insert([
                'title' => $title,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

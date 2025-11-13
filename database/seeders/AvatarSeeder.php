<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Avatar;

class AvatarSeeder extends Seeder
{
    public function run(): void
    {
        $avatars = [
            ['name' => 'Male Avatar 1', 'image' => 'avatars/man1.jpeg', 'gender' => 'male'],
            ['name' => 'Male Avatar 2', 'image' => 'avatars/man2.jpeg', 'gender' => 'male'],
              ['name' => 'Male Avatar 3', 'image' => 'avatars/man3.jpeg', 'gender' => 'male'],
            ['name' => 'Male Avatar 4', 'image' => 'avatars/man4.jpeg', 'gender' => 'male'],
              ['name' => 'Male Avatar 5', 'image' => 'avatars/man5.jpeg', 'gender' => 'male'],
            ['name' => 'Male Avatar 6', 'image' => 'avatars/man6.jpeg', 'gender' => 'male'],
              ['name' => 'Male Avatar 7', 'image' => 'avatars/man7.jpeg', 'gender' => 'male'],
            ['name' => 'Male Avatar 8', 'image' => 'avatars/man8.jpeg', 'gender' => 'male'],
              ['name' => 'Male Avatar 9', 'image' => 'avatars/man9.jpeg', 'gender' => 'male'],


            ['name' => 'Female Avatar 1', 'image' => 'avatars/girl1.jpeg', 'gender' => 'female'],
            ['name' => 'Female Avatar 2', 'image' => 'avatars/girl2.jpeg', 'gender' => 'female'],
            ['name' => 'Female Avatar 3', 'image' => 'avatars/girl3.jpeg', 'gender' => 'female'],
              ['name' => 'Female Avatar 4', 'image' => 'avatars/girl4.jpeg', 'gender' => 'female'],
            ['name' => 'Female Avatar 5', 'image' => 'avatars/girl5.jpeg', 'gender' => 'female'],
        ];

        foreach ($avatars as $avatar) {
            Avatar::updateOrCreate(['image' => $avatar['image']], $avatar);
        }
    }
}

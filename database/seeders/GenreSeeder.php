<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genreNames = ['roman', 'tragic', 'fantasy'];
        $genres = [];

        for ($i = 0; $i < 3; $i++) {
            $genres[] = [
                'title' => $genreNames[$i],
                'is_active' => 1,
            ];
        }

        DB::table('genres')->insert($genres);
    }
}

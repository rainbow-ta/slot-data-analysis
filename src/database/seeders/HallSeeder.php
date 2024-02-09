<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('halls')->insert([
            [
                'name' => '三ノ輪UNO',
                'ana_slot_url_name' => '三ノ輪uno',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

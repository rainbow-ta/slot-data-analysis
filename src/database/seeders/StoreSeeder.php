<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('stores')->insert([
            [
                'name' => '三ノ輪UNO',
                'ana_slot_url_name' => '三ノ輪uno',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ジュラク王子',
                'ana_slot_url_name' => 'ジュラク王子店',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

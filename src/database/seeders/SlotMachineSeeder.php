<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SlotMachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('slot_machines')->insert([
            [
                'name' => 'マイジャグラーV',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

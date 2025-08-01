<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('departments')->insert([
            ['name' => 'Marketing', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Production and Tech', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Administration', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}

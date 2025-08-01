<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Loop untuk menambahkan 20 data employee
        for ($i = 0; $i < 20; $i++) {
            DB::table('employees')->insert([
                'nama_lengkap'    => $faker->name,
                'email_asana'     => $faker->unique()->safeEmail,
                'no_telepon'      => $faker->phoneNumber,
                'username_github' => $faker->userName,
                'department_id'   => rand(1, 3), // Asumsi ada 5 department
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}

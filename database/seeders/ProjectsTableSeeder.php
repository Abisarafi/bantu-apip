<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('projects')->insert([
            [
                'id' => Str::uuid(), // Generate UUID
                'project_name' => 'Project Management System',
                'asana_link' => 'https://app.asana.com/0/123456789/example-project',
                'github_repo_link' => 'https://github.com/example/project-management',
                'gid_project' => '123456789',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'project_name' => 'E-Commerce Website',
                'asana_link' => 'https://app.asana.com/0/987654321/e-commerce-project',
                'github_repo_link' => 'https://github.com/example/ecommerce-website',
                'gid_project' => '987654321',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'project_name' => 'Mobile Application',
                'asana_link' => 'https://app.asana.com/0/1122334455/mobile-app-project',
                'github_repo_link' => 'https://github.com/example/mobile-application',
                'gid_project' => '1122334455',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

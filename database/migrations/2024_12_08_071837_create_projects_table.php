<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('project_name', 255);
            $table->string('asana_link', 2083);
            $table->string('gid_project')->unique();
            $table->string('github_repo_link', 2083);
            $table->string('github_repo_link_fe', 2083)->nullable();
            $table->string('github_repo_link_mobile', 2083)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

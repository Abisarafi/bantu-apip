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
        Schema::create('project_managers', function (Blueprint $table) {
            $table->uuid('id')->primary(); // ⬅️ Pastikan ini UUID
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('nama_pm');
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('project_manager_projects', function (Blueprint $table) {
            // $table->uuid('id')->primary();
            $table->uuid('project_manager_id');
            $table->uuid('project_id');
            $table->boolean('is_notified')->default(true);
            $table->timestamps();
            $table->foreign('project_manager_id')->references('id')->on('project_managers')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('project_manager_projects', function (Blueprint $table) {
            $table->dropForeign(['project_manager_id']);
            $table->dropForeign(['project_id']);
        });
        Schema::dropIfExists('project_managers');
        Schema::dropIfExists('project_manager_projects');
    }
};

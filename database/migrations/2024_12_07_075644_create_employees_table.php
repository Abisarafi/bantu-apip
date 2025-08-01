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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('email_asana')->unique();
            $table->string('no_telepon');
            $table->string('username_github')->nullable();
            $table->unsignedBigInteger('department_id');
            $table->timestamps();

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onDelete('cascade'); // Hapus employee jika department terkait dihapus
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

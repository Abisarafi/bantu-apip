<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('employee_tracked_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');  // Menyimpan relasi ke employee
            $table->string('tracked_time_id')->unique();  // ID dari Jibble untuk memastikan data tidak duplikat
            $table->string('tracked_time');  // Durasi waktu dari Jibble
            $table->date('tracked_date');  // Tanggal waktu (misalnya tanggal kerja)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_tracked_times');
    }
};

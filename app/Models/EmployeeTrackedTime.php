<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTrackedTime extends Model
{
    use HasFactory;

    protected $table = 'employee_tracked_times';  // Nama tabel yang kita buat

    // Tentukan field yang bisa diisi (mass assignable)
    protected $fillable = [
        'employee_id',          // Relasi ke employee
        'tracked_time_id',      // ID dari Jibble
        'tracked_time',         // Durasi waktu yang dicatat
        'tracked_date',         // Tanggal waktu yang tercatat
    ];

    // Relasi dengan tabel Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{

    use HasFactory;
    protected $fillable = [
        'nama_lengkap',
        'email_asana',
        'no_telepon',
        'id_jibble',
        'username_github',
        'department_id',
        'story_point_target',
        'working_hour_target',
    ];
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function trackedTimes()
    {
        return $this->hasMany(EmployeeTrackedTime::class);
    }
}

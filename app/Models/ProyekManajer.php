<?php

namespace App\Models;

use App\Models\ProjectLink;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProyekManajer extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'project_managers';

    protected $fillable = ['user_id', 'nama_pm', 'email'];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_manager_projects', 'project_manager_id', 'project_id')
            ->withPivot('is_notified')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }

    protected $keyType = 'string';
    public $incrementing = false;
}

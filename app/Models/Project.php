<?php

namespace App\Models;

use App\Models\ProjectLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasUuids;
    protected $fillable = [
        'project_name',
        'asana_link',
        'gid_project',
        'github_repo_link',
        'github_repo_link_fe',
        'github_repo_link_mobile',

    ];
    protected $keyType = 'string';
    public $incrementing = false;

    /*************  ✨ Codeium Command ⭐  *************/
    /******  36a962ea-c1f6-405d-be1e-d8ba532108bc  *******/
    public function project_links()
    {
        return $this->hasMany(ProjectLink::class);
    }


    public function projectManagers()
    {
        return $this->belongsToMany(ProyekManajer::class, 'project_manager_projects')
            ->withPivot('is_notified')
            ->withTimestamps();
    }
}

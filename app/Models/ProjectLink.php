<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Project;

class ProjectLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'link',
    ];

    // Relasi balik ke tabel projects
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSection extends Model
{
    use HasFactory;
    use EnterpriseScopeTrait;

    protected $fillable = [
        'enterprise_id',
        'company_id',
        'project_id',
        'name',
        'section_description',
        'progress',
        'section_progress',
    ];

    protected $appends = ['name_text'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    static public function boot()
    {
        parent::boot();

        static::created(function ($model) {
            Project::update_progress($model->project_id);
        });
        static::updated(function ($model) {
            Project::update_progress($model->project_id);
        });

        //create for creating
        static::creating(function ($model) {
            // Enterprise ID will be auto-set by EnterpriseScopeTrait
            $user = auth()->user();
            if ($user && $user->company_id) {
                $model->company_id = $user->company_id;
            }
        });

        static::deleted(function ($model) {
            Project::update_progress($model->project_id);
        });
    }

    public static function getArray($where = [])
    {
        $sections = ProjectSection::where($where)->get();
        $array = [];
        foreach ($sections as $section) {
            $array[$section->id] = $section->name_text;
        }
        return $array;
    }

    public function getNameTextAttribute()
    {
        if ($this->project == null) {
            return $this->name;
        }
        return  $this->project->short_name . ' - ' . $this->name;
    }
}

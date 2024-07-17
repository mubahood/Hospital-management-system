<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSection extends Model
{
    use HasFactory;
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
            $model->company_id = auth()->user()->company_id;
        });

        static::deleted(function ($model) {
            Project::update_progress($model->project_id);
        });
    }


    public static function get_array($where = [])
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

    protected $appends = ['name_text'];

    protected $fillable = [
        'company_id',
        'project_id',
        'name',
        'section_description',
        'progress',
        'section_progress',
    ];
}

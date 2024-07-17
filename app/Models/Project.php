<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    public static function get_array($where = [])
    {
        $sections = Project::where($where)
        ->orderBy('short_name', 'asc')
        ->get();
        $array = [];
        foreach ($sections as $section) {
            $array[$section->id] = $section->short_name;
        }
        return $array;
    }


    //boot
    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($project) {
            //if id is 1, do not delete
            if ($project->id == 1) {
                return false;
            }
        });
    }

    public static function update_progress($project_id)
    {
        $project = Project::find($project_id);
        if ($project == null) {
            return;
        }
        $sections = ProjectSection::where('project_id', $project_id)->get();
        $progress = 0;
        $section_progress = 0;
        foreach ($sections as $section) {
            $section_progress += (int)$section->progress;
        }
        if (count($sections) > 0) {
            $progress = $section_progress / count($sections);
        }
        $project->progress = $progress;
        $project->save();
    }

    public function project_sections()
    {
        return $this->hasMany(ProjectSection::class);
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getOtherClientsAttribute($value)
    {
        if ($value == null) {
            return [];
        }
        if (strlen($value) < 1) {
            return [];
        }
        try {
            //$value = explode(',', $value);
        } catch (\Exception $e) {
            $value = [];
        }
        return $value;
    }

    public function setOtherClientsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['other_clients'] = implode(',', $value);
        }
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function manager()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }
}

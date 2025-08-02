<?php

namespace App\Models;

use App\Traits\EnterpriseScopeTrait;
use App\Traits\StandardBootTrait;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory, EnterpriseScopeTrait, StandardBootTrait;

    protected $fillable = [
        'enterprise_id',
        'name',
        'short_name',
        'description',
        'details',
        'logo',
        'budget_overview',
        'schedule_overview',
        'risks_issues',
        'concerns_recommendations',
        'status',
        'company_id',
        'administrator_id'
    ];

    public static function getArray($where = [])
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
    protected static function boot(): void
    {
        parent::boot();
        
        // Call standardized boot methods
        static::bootStandardBootTrait();
    }

    /**
     * Handle pre-deletion logic - called by StandardBootTrait
     */
    protected static function onDeleting($model): bool
    {
        //if id is 1, do not delete
        if ($model->id == 1) {
            return false;
        }
        return true;
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

    public function projectSections()
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

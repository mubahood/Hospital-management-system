<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    use HasFactory;

    //fillables
    protected $fillable = [
        'company_id',
        'project_id',
        'project_section_id',
        'assigned_to',
        'created_by',
        'manager_id',
        'name',
        'task_description',
        'due_to_date',
        'delegate_submission_status',
        'delegate_submission_remarks',
        'manager_submission_status',
        'manager_submission_remarks',
        'priority',
        'meeting_id',
    ];


    static public function boot()
    {
        parent::boot();

        static::created(function ($model) {
            User::update_rating($model->assigned_to);
            Project::update_progress($model->project_id);
            //send notification to the assigned user
            Task::send_notification($model);
        });
        static::updated(function ($model) {
            User::update_rating($model->assigned_to);
            Project::update_progress($model->project_id);
        });
        static::deleted(function ($model) {
            User::update_rating($model->assigned_to);
            Project::update_progress($model->project_id);
        });

        static::creating(function ($model) {

            /*             if (
                $model->manager_submission_status == null ||
                strlen($model->manager_submission_status) < 2
            ) {

            } */
          /*   $model->manager_submission_status = 'Not Submitted';
            $model->delegate_submission_status = 'Not Submitted'; */
            /*             if (
                $model->delegate_submission_status == null ||
                strlen($model->delegate_submission_status) < 2
            ) {

            } */

            if($model->manager_submission_status == null || strlen($model->manager_submission_status) < 3){
                $model->manager_submission_status = 'Not Submitted';
            }
            if($model->delegate_submission_status == null || strlen($model->delegate_submission_status) < 3){
                $model->delegate_submission_status = 'Not Submitted';
            }



            $model->rate = 0;
            if ($model->manager_submission_status == 'Not Submitted') {
                $model->rate = 0;
            } else if ($model->manager_submission_status == 'Done') {
                $model->rate = 6;
            } else if ($model->manager_submission_status == 'Done Late') {
                $model->rate = 3;
            } else if ($model->manager_submission_status == 'Not Attended To') {
                $model->rate = -6;
            }

            if ($model->priority == null || $model->priority == '') {
                $model->priority = 'Medium';
            }

            $model->company_id = auth()->user()->company_id;
            $model->created_by = auth()->user()->id;

            if ($model->assign_to_type == 'to_me') {
                $model->assigned_to = Auth::user()->id;
            }

            $assigned_to_user = Administrator::find($model->assigned_to);
            $created_by_user = Administrator::find($model->created_by);

            if ($created_by_user != null) {
                if ($assigned_to_user != null) {
                    if ($assigned_to_user->id != $created_by_user->id) {
                        $model->assign_to_type = 'to_other';
                    } else {
                        $model->assign_to_type = 'to_me';
                    }
                }
            }

            $model = Task::prepare_saving($model);
        });


        static::updating(function ($model) {
            $model->rate = 0;
            if (
                $model->manager_submission_status == null ||
                strlen($model->manager_submission_status) < 2
            ) {
                $model->manager_submission_status = 'Not Submitted';
            }
            if (
                $model->delegate_submission_status == null ||
                strlen($model->delegate_submission_status) < 2
            ) {
                $model->delegate_submission_status = 'Not Submitted';
            }
            if ($model->manager_submission_status == 'Not Submitted') {
                $model->rate = 0;
            } else if ($model->manager_submission_status == 'Done') {
                $model->rate = 6;
            } else if ($model->manager_submission_status == 'Done Late') {
                $model->rate = 3;
            } else if ($model->manager_submission_status == 'Not Attended To') {
                $model->rate = -6;
            }
            $model = Task::prepare_saving($model);
        });
    }

    public static function prepare_saving($model)
    {

        $project_section = ProjectSection::find($model->project_section_id);
        if ($project_section != null) {
            $model->project_id = $project_section->project_id;
        }
        $assigned_to_user = Administrator::find($model->assigned_to);
        $manager_user = Administrator::find($assigned_to_user->managed_by);
        if ($manager_user != null) {
            $model->manager_id = $manager_user->id;
        } else {
            $model->manager_id = $assigned_to_user->id;
        }

        if (
            /* $model->manager_submission_status != 'Not Submitted' && */
            $model->delegate_submission_status != 'Not Submitted'
        ) {
            $model->is_submitted = 'Yes';
        } else {
            $model->is_submitted = 'No';
        }
        return $model;
    }

    public function assigned_to_user()
    {
        return $this->belongsTo(Administrator::class, 'assigned_to');
    }
    public function created_by_user()
    {
        return $this->belongsTo(Administrator::class, 'created_by');
    }
    public function manager_user()
    {
        return $this->belongsTo(Administrator::class, 'manager_id');
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function project_section()
    {
        return $this->belongsTo(ProjectSection::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    //send notification to the assigned user
    public static function send_notification($model)
    {
        $created_by = Administrator::find($model->created_by);
        $assigned_to = Administrator::find($model->assigned_to);
        if ($created_by == null || $assigned_to == null) {
            return;
        }
        if ($created_by->id == $assigned_to->id) {
            return;
        }

        //check if $assigned_to->email mail is not valid and return
        if (!filter_var($assigned_to->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        //mail message 
        $message = <<<EOT
Dear $assigned_to->name,
You have been assigned a new task by $created_by->name.
Please login to the App to attend to it.
<b>Task:</b> $model->name
<b>Due to date:</b> $model->due_to_date
<b>Priority:</b> $model->priority
<b>Task description:</b> $model->task_description
<b>Assigned by:</b> $created_by->name
Thank you.
Regards,
System Administrator.
This is an automated message, please do not reply.
EOT;


        //date format of Monday - 2021-09-06 
        $date = date('l - Y-m-d', strtotime($model->created_at));
        $data['email'] = $assigned_to->email;
        $data['name'] = $assigned_to->name;
        $data['subject'] = "New task assigned to you - $date";
        $data['body'] = $message;
        $data['view'] = 'mail';
        $data['data'] = $message;
        try {
            Utils::mail_sender($data);
            $model->is_sent = 'Sent';
            $model->save();
        } catch (\Throwable $th) {
            try {
                $model->is_sent = 'Failed';
                $model->sent_failed_reason = $th->getMessage();
                $model->save();
            } catch (\Throwable $th) {
            }
            return;
        }
    }

    //appends assigned_to_text
    public function getAssignedToTextAttribute()
    {
        $assigned_to_user = Administrator::find($this->assigned_to);
        if ($assigned_to_user == null) {
            return '';
        }
        return $assigned_to_user->name;
    }

    //appends assigned_to_text
    protected $appends = ['assigned_to_text'];
}

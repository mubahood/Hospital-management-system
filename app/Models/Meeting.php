<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    public function setAttendanceListPicturesAttribute($pictures)
    {
        if (is_array($pictures)) {
            $this->attributes['attendance_list_pictures'] = json_encode($pictures);
        }
    }
    public function getAttendanceListPicturesAttribute($pictures)
    {
        if ($pictures != null)
            return json_decode($pictures, true);
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function send_mails()
    {
        $m = $this;
        $done = [];
        foreach ($m->tasks as $key => $task) {
            if (in_array($task->manager_id, $done)) {
                continue;
            }
            $done[] = $task->manager_id;
        }
        foreach ($done as $key => $d) {
            $u = User::find($d);
            if ($u == null) {
                continue;
            }
            $count_tasks = Task::where([
                'manager_id' => $d,
                'meeting_id' => $m->id,
            ])->count();

            //mail message 
            $message = "Dear " . $u->name . ",\n";
            $message .= "You have " . $count_tasks . " tasks to attend to in the meeting " . $m->name . ".\n";
            $message .= "Please login to the App to attend to them.\n";
            $message .= "Thank you.\n";
            $message .= "Regards,\n";
            $message .= "System Administrator.\n";
            $message .= "This is an automated message, please do not reply.\n";


            $u->email = 'mubahood360@gmail.com';
            $data['email'] = $u->email;
            $data['name'] = $u->name;
            $data['subject'] = "Supervision tasks for meeting " . $m->name . "";
            $data['body'] = $message;
            $data['view'] = 'mail';
            $data['data'] = $message;
            try {
                Utils::mail_sender($data);
                $m->is_sent = 'Sent';
                $m->save();
            } catch (\Throwable $th) {
                try {
                    $m->is_sent = 'Failed';
                    $m->sent_failed_reason = $th->getMessage();
                    $m->save();
                } catch (\Throwable $th) {
                }
                return;
            }
        }
    }
}

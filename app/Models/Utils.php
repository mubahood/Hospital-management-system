<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use SplFileObject;
use Zebra_Image;

class Utils extends Model
{
    use HasFactory;

    //static function generate_uuid
    static function generate_uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    //pluralize word static function
    public static function pluralize($word)
    {
        $last = $word[strlen($word) - 1];
        if ($last == 'y') {
            $word = substr($word, 0, -1) . 'ies';
        } else {
            $word .= 's';
        }
        return $word;
    }

    //static short
    public static function short($text, $limit = 100)
    {
        if (strlen($text) > $limit) {
            return substr($text, 0, $limit) . "...";
        }
        return $text;
    }

    //get date when this week started
    public static function week_started($date)
    {
        $date = Carbon::parse($date);
        $date->startOfWeek();
        return $date;
    }
    //get date when this week ended
    public static function week_ended($date)
    {
        $date = Carbon::parse($date);
        $date->endOfWeek();
        return $date;
    }

    //manifest
    public static function manifest($u)
    {
        $week_start = Utils::week_started(Carbon::now());
        $week_end = Utils::week_ended(Carbon::now());

        $ob = new \stdClass();
        $ob->tasks_pending_items = Task::where([
            'assigned_to' => $u->id,
            'is_submitted' => 'No',
        ])->get();


        $ob->manage_tasks = Task::where([
            'manager_id' => $u->id,
            'is_submitted' => 'No',
        ])->get();

        $ob->tasks_completed = Task::where([
            'assigned_to' => $u->id,
            'is_submitted' => 'Yes',
        ])->get();

        $ob->tasks_pending = $ob->tasks_pending_items->count();

        $tasks_missed = [
            'assigned_to' => $u->id,
            'is_submitted' => 'Yes',
            'company_id' => $u->company_id,
            'manager_submission_status' => 'Not Attended To',
        ];
        $tasks_done = [
            'assigned_to' => $u->id,
            'is_submitted' => 'Yes',
            'manager_submission_status' => 'Done',
            'company_id' => $u->company_id,
        ];
        if ($u->can('admin')) {
            unset($tasks_missed['assigned_to']);
            unset($tasks_done['assigned_to']);
        }

        //for this week only
        $ob->tasks_missed = Task::where($tasks_missed)
            ->whereBetween('due_to_date', [$week_start, $week_end])
            ->count();
        $ob->tasks_done = Task::where($tasks_done)
            ->whereBetween('due_to_date', [$week_start, $week_end])
            ->count();

        $ob->targets = Target::where([
            'company_id' => $u->company_id,
            'status' => 'Pending',
        ])
            ->orderby('id', 'desc')
            ->limit(5)
            ->get();
        $ob->milestones = Target::where([
            'company_id' => $u->company_id,
            'status' => 'Completed',
            'type' => 'Milestone',
        ])
            ->orderby('id', 'desc')
            ->limit(5)
            ->get();

        //get projects weights
        $progresses = [];
        foreach (
            Project::where([
                'company_id' => $u->company_id,
                'status' => 'Active',
            ])->get() as $key => $project
        ) {
            $progress = [];
            $progress['id'] = $project->id;
            $progress['name'] = $project->short_name;
            $progress['project'] = $project;
            if ($project->progress < 50) {
                $color = 'red';
            } else if ($project->progress < 75) {
                $color = 'yellow';
            } else {
                $color = 'green';
            }

            $end_date = Carbon::parse($project->end_date);
            $now = Carbon::now();
            if ($end_date->lt($now)) {
                $color = 'red';
            }

            $progress['progress'] = $project->progress;
            $progress['color'] = $color;
            $progress['tasks'] = Task::where([
                'project_id' => $project->id,
                'company_id' => $u->company_id,
                'is_submitted' => 'No',
            ])->sum('hours');
            $progresses[] = $progress;
        }
        $ob->project_weights = $progresses;
        $progress = Project::where([
            'company_id' => $u->company_id,
            'status' => 'Active',
        ])->sum('progress');
        $total_projects = $progress / Project::where([
            'company_id' => $u->company_id,
            'status' => 'Active',
        ])->count();
        $ob->total_projects_progress = round($total_projects, 2);
        $ob->total_projects_progress_remaining = 100 - $ob->total_projects_progress;


        /* 
        $table->string('')->nullable()->default('No');
        $table->integer('work_load_pending')->nullable()->default(0);
        $table->integer('work_load_completed')->nullable()->default(0);
        */
        $employees = User::where([
            'company_id' => $u->company_id,
            'can_evaluate' => 'Yes',
        ])
            /*         ->where('work_load_pending', '>', 0) */
            ->get();

        //my pending tasks

        $ob->employees = $employees;
        return $ob;
    }

    public static function validateEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }



    //mail sender
    public static function mail_sender($data)
    {
        try {
            Mail::send(
                'mail',
                [
                    'body' => view('mail-1', [
                        'body' => $data['body'],
                    ]),
                    'title' => $data['subject']
                ],
                function ($m) use ($data) {
                    $m->to($data['email'], $data['name'])
                        ->subject($data['subject']);
                    $m->from(env('MAIL_USERNAME', 'noreply@taskease.net'), $data['subject']);
                }
            );
        } catch (\Throwable $th) {
            $msg = 'failed';
            throw $th;
        }
    }


    public static function response($data = [])
    {
        header('Content-Type: application/json; charset=utf-8');
        $resp['status'] = "1";
        $resp['code'] = "1";
        $resp['message'] = "Success";
        $resp['data'] = null;
        if (isset($data['status'])) {
            $resp['status'] = $data['status'] . "";
            $resp['code'] = $data['status'] . "";
        }
        if (isset($data['message'])) {
            $resp['message'] = $data['message'];
        }
        if (isset($data['data'])) {
            $resp['data'] = $data['data'];
        }
        return $resp;
    }

    //static php fuction that greets the user according to the time of the day
    public static function greet()
    {
        $hour = date('H');
        if ($hour > 0 && $hour < 12) {
            return "Good Morning";
        } else if ($hour >= 12 && $hour < 17) {
            return "Good Afternoon";
        } else if ($hour >= 17 && $hour < 19) {
            return "Good Evening";
        } else {
            return "Good Night";
        }
    }



    public static function prepare_calendar_tasks($u)
    {

        $raws = Consultation::all();
        $events = [];

        foreach ($raws as $key => $task) {
            $ev['activity_id'] = $task->id;
            $event_date_time = Carbon::parse($task->created_at);
            $ev['title'] = $task->services_requested;
            $ev['name'] = $task->consultation_number;
            $event_date = $event_date_time->format('Y-m-d');
            $event_time = $event_date_time->format('h:m a');
            $ev['url_edit'] = admin_url('consultations/' . $task->id . '/edit');
            $ev['url_view'] = admin_url('medical-report?id=' . $task->id);
            $ev['status'] = $task->main_status;

            $ev['classNames'] = ['bg-primary', 'border-primary', 'text-dark'];
            if (
                $task->main_status == 'Completed'
            ) {
                $ev['status'] = 'Done';
                $ev['classNames'] = ['bg-success', 'border-success', 'text-white'];
            } else if (
                $task->main_status == 'Rejected' ||
                $task->main_status == 'Cancelled'
            ) {
                $ev['status'] = 'Cancelled';
                $ev['classNames'] = ['bg-danger', 'border-danger', 'text-white'];
            } else {
                $ev['status'] = 'Not Submitted';
                $ev['status'] = 'Ongoing';
            }

            /* 
                'Pending' => 'Pending',
                'Completed' => 'Completed',
                'Ongoing' => 'Ongoing',
                'Rejected' => 'Rejected',
                'Cancelled' => 'Cancelled',
                'Approved' => 'Approved',
            */

            $details = $task->instruction . '<br><br>';

            $details .= "<b> Date:</b> {$event_date}<br>";
            //limit description to 100 characters

            if (strlen($task->task_description) > 100) {
                $description = substr(strip_tags($task->task_description), 0, 100) . "...";
            } else {
                $description = $task->task_description;
            }
            $details .= "<br><b>Description:</b> {$description}<br>";
            $ev['details'] = $details;
            $ev['start'] = Carbon::parse($event_date)->format('Y-m-d');
            $events[] = $ev;
        }

        return $events;
    }




    public static function prepare_calendar_events($u)
    {
        return self::prepare_calendar_tasks($u);
        $conditions = [
            'company_id' => $u->company_id,
        ];
        $eves = Event::where($conditions)->get();
        $events = [];
        foreach ($eves as $key => $event) {
            $ev['activity_id'] = $event->id;
            $event_date_time = Carbon::parse($event->event_date);
            $ev['title'] = $event_date_time->format('h:m ') . $event->name;
            $event_date = $event_date_time->format('Y-m-d');
            $event_time = $event_date_time->format('h:m a');
            $ev['name'] = $event->name;
            $ev['url_edit'] = admin_url('events/' . $event->id . '/edit');
            $ev['url_view'] = admin_url('events/' . $event->id);
            $ev['status'] = $event->event_conducted;
            $ev['classNames'] = ['bg-warning', 'border-warning', 'text-dark'];
            if ($event->event_conducted == 'Conducted') {
                $ev['status'] = 'Conducted';
                $ev['classNames'] = ['bg-success', 'border-success', 'text-white'];
            } else if ($event->event_conducted == 'Skipped') {
                $ev['status'] = 'Skipped';
                $ev['classNames'] = ['bg-danger', 'border-danger', 'text-white'];
            } else {
                $ev['status'] = 'Pending';
            }

            $details = $event->name . '<br><br>';

            $details .= "<b>Event Date:</b> {$event_date}<br>";
            $details .= "<b>Time:</b> {$event_time}<br>";
            $details .= "<b>Status:</b> {$event->event_conducted}<br>";
            //limit description to 100 characters

            if (strlen($event->description) > 100) {
                $description = substr(strip_tags($event->description), 0, 100) . "...";
            } else {
                $description = $event->description;
            }
            $details .= "<br><b>Description:</b> {$description}<br>";
            $ev['details'] = $details;
            $ev['start'] = Carbon::parse($event_date)->format('Y-m-d');
            $events[] = $ev;
        }
        return $events;
    }


    public static function success($data = [], $message = "")
    {
        return (response()->json([
            'code' => 1,
            'message' => $message,
            'data' => $data
        ]));
    }

    public static function error($message = "")
    {
        return response()->json([
            'code' => 0,
            'message' => $message,
            'data' => ""
        ]);
    }



    public static function importPwdsProfiles($path)
    {
        $csv = new SplFileObject($path);
        $csv->setFlags(SplFileObject::READ_CSV);
        //$csv->setCsvControl(';');  //separator change if you need
        set_time_limit(-1); // Time in seconds
        $disability_description = [];
        $cats = [];
        $isFirst  = true;
        foreach ($csv as $line) {
            if ($isFirst) {
                $isFirst = false;
                continue;
            }

            $name = $line[0];
            $user = Person::where(['name' => $name])->first();
            if ($user == null) {
                continue;
            }
            $user->district_id = 88;
            $user->parish .= 1;
            $user->save();
            continue;



            /* if ((Person::count('id') >= 3963)) {
                die("done");
            } */

            $p = new Person();
            $p->name = 'N/A';



            $p->subcounty_description = null;
            if (
                isset($line[10]) &&
                $line[10] != null &&
                strlen($line[10]) > 2
            ) {
                $dis = $line[10];
                $_dis = Location::where(
                    'name',
                    'LIKE',
                    '%' . $dis . '%'
                )->first();
                if ($_dis != null) {
                    $p->district_id = $_dis->id;
                } else {
                    $p->district_id = 1002006;
                }
            }


            $p->subcounty_description = null;
            if (
                isset($line[8]) &&
                $line[8] != null &&
                strlen($line[8]) > 1
            ) {
                $p->dob = $line[8];
            }

            $p->subcounty_description = null;
            if (
                isset($line[7]) &&
                $line[7] != null &&
                strlen($line[7]) > 3
            ) {
                $p->caregiver_name = $line[7];
                $p->has_caregiver = 'Yes';
            } else {
                $p->has_caregiver = 'No';
            }

            $p->subcounty_description = null;
            if (
                isset($line[4]) &&
                $line[4] != null &&
                strlen($line[4]) > 3
            ) {
                $p->disability_description = $line[4];
            }

            $p->education_level = null;
            if (
                isset($line[5]) &&
                $line[5] != null &&
                strlen($line[5]) > 1
            ) {
                //$p->education_level = $line[5];
            }

            $p->job = null;
            if (
                isset($line[6]) &&
                $line[6] != null &&
                strlen($line[6]) > 1
            ) {
                $p->employment_status = 'Yes';
                $p->job = $line[6];
            } else {
                $p->employment_status = 'No';
            }

            if (
                isset($line[0]) &&
                $line[0] != null &&
                strlen($line[0]) > 2
            ) {
                $p->name = trim($line[0]);
            }

            $p->sex = 'N/A';
            if (
                isset($line[1]) &&
                $line[1] != null &&
                strlen($line[1]) > 0
            ) {
                if (strtolower(substr($line[0], 0, 1)) == 'm') {
                    $p->sex = 'Male';
                } else {
                    $p->sex = 'Female';
                }
            }

            $p->phone_number_1 = null;
            if (
                isset($line[2]) &&
                $line[2] != null &&
                strlen($line[2]) > 5
            ) {
                $p->phone_number_1 = Utils::prepare_phone_number($line[2]);
            }

            if (
                isset($line[3]) &&
                $line[3] != null &&
                strlen($line[3]) > 2
            ) {
                $cat =  trim(strtolower($line[3]));

                if (in_array($cat, [
                    'epilepsy'
                ])) {
                    $p->disability_id = 1;
                    $p->disability_description = $line[3];
                } elseif (in_array($cat, [
                    'visual',
                    'visual impairment',
                    'deaf-blind',
                    'visual disability',
                    'visual impairmrnt',
                    'blind',
                ])) {
                    $p->disability_id = 2;
                    $p->disability_description = $line[3];
                } elseif (in_array($cat, [
                    'deaf',
                    'epileosy/hard of speach',
                    'hard of hearing',
                    'hearing impairment',
                    'deaf blindness',
                    'hearing impairment',
                    'deaf-blind',
                    'youth rep (deaf )',
                    'deaf rep',
                    'deaf rep.',
                    'deaf',
                    'deafblind',
                ])) {
                    $p->disability_id = 3;
                    $p->disability_description = $line[3];
                } elseif (in_array($cat, [
                    'visual disabilty',
                    "low vision",
                    "visual",
                    "visual impairment",
                ])) {
                    $p->disability_id = 4;
                    $p->disability_description = $line[3];
                } elseif (in_array($cat, [
                    'intellectual disability',
                    'mental disabilty',
                    'mental disability',
                    'intellectual',
                    'interlectual',
                    'parent with interlectual',
                    'interlectual rep.',
                    'cerebral pulse',
                    'mental',
                    'mental retardation',
                    'mental health',
                    'mental illness',
                ])) {

                    $p->disability_id = 5;
                    $p->disability_description = $line[3];
                } elseif (in_array($cat, [
                    'epileptic',
                    'parent with children with intellectual disability',
                    'brain injury',
                    'spine damage',
                    'epilipsy',
                    'person with epilepsy',
                    'epilepsy',
                    'hydrosphlus',
                    'epilpesy',
                    'celebral palsy',
                    'women rep .celebral palsy',
                ])) {

                    $p->disability_id = 6;
                    $p->disability_description = $line[3];
                } elseif (in_array($cat, [
                    'physical',
                    'parent',
                    'physical  disability',
                    'physical disability',
                    'physical disabbility',
                    'physical disabilty',
                    'pyhsical disability',
                    'physical didability',
                    'physical diability',
                    'physical impairment',
                    'male',
                    'amputee',
                    'sickler',
                    'physical',
                    'physical impairment',
                    'parent rep',
                    'women rep.',
                    'youth rep',
                    'parent rep.',
                    'parent  rep.',
                    'parent',
                    'youth rep,',
                    'women rep',
                    'youth rep.',
                ])) {
                    $p->disability_id = 7;
                    $p->disability_description = $line[3];
                } elseif (in_array($cat, [
                    'albino',
                    'albinism',
                    'person with albinism',
                    'albism',
                    'albino',
                    'albinsim',
                    'albinism',
                ])) {
                    $p->disability_id = 8;
                    $p->disability_description = $line[3];
                } elseif (in_array($cat, [
                    'little person',
                    'littleperson',
                    'liitleperson',
                    'liittleperson',
                    'little person',
                    'dwarfism',
                    'persons of short stature (little persons)',
                ])) {
                    $p->disability_id = 9;
                    $p->disability_description = $line[3];
                } else {
                    $p->disability_id = 7;
                    $p->disability_description = $line[3];
                }
            } else {
                $p->disability_id = 6;
                $p->disability_description = 'Other';
            }

            $p->subcounty_description = null;
            if (
                isset($line[2]) &&
                $line[2] != null &&
                strlen($line[2]) > 5
            ) {
                $p->phone_number_1 = Utils::prepare_phone_number($line[2]);
            }

            $_p = Person::where(['name' => $p->name, 'district_id' => $p->district_id])->first();
            if ($_p != null) {
                echo "FOUND => $_p->name<=========<hr>";
                continue;
            }

            try {
                $p->save();
                echo $p->id . ". " . $p->name . "<hr>";
            } catch (\Throwable $th) {
                echo $th;
                echo "failed <br>";
            }
        }

    }





    public static function phone_number_is_valid($phone_number)
    {
        $phone_number = Utils::prepare_phone_number($phone_number);
        if (substr($phone_number, 0, 4) != "+256") {
            return false;
        }

        if (strlen($phone_number) != 13) {
            return false;
        }

        return true;
    }
    public static function prepare_phone_number($phone_number)
    {
        $original = $phone_number;
        //$phone_number = '+256783204665';
        //0783204665
        if (strlen($phone_number) > 10) {
            $phone_number = str_replace("+", "", $phone_number);
            $phone_number = substr($phone_number, 3, strlen($phone_number));
        } else {
            if (substr($phone_number, 0, 1) == "0") {
                $phone_number = substr($phone_number, 1, strlen($phone_number));
            }
        }
        if (strlen($phone_number) != 9) {
            return $original;
        }
        return "+256" . $phone_number;
    }



    public static function docs_root()
    {
        $r = $_SERVER['DOCUMENT_ROOT'] . "";


        //check if $_SERVER['HTTP_HOST'] is contains locahost
        if (
            (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) ||
            (strpos($_SERVER['HTTP_HOST'], '10.0.2.2') !== false)
        ) {
            $script = $_SERVER['SCRIPT_FILENAME'];
            $s = rtrim($script, 'server.php');

            return $s . 'public/';
        }

        if (!str_contains($r, 'home/')) {
            $r = str_replace('/public', "", $r);
            $r = str_replace('\public', "", $r);
        }

        $isOnline = false;
        if (isset($_SERVER['HTTP_HOST'])) {
            $server = strtolower($_SERVER['HTTP_HOST']);
            if (str_contains($server, 'schooldynamics.ug')) {
                $isOnline = true;
            }
        }

        if ($isOnline) {
            $r = $_SERVER['DOCUMENT_ROOT'] . "";
        }

        $r = $r . "/public/";

        /*oot
         "/home/ulitscom_html/public/storage/images/956000011639246-(m).JPG

        public_html/public/storage/images
        */
        if ($isOnline) {
            $r = $_SERVER['DOCUMENT_ROOT'] . "/public/";
        }
        return $r;
    }


    public static function upload_images_2($files, $is_single_file = false)
    {

        ini_set('memory_limit', '-1');
        if ($files == null || empty($files)) {
            return $is_single_file ? "" : [];
        }
        $uploaded_images = array();
        foreach ($files as $file) {

            if (
                isset($file['name']) &&
                isset($file['type']) &&
                isset($file['tmp_name']) &&
                isset($file['error']) &&
                isset($file['size'])
            ) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $file_name = time() . "-" . rand(100000, 1000000) . "." . $ext;
                $destination = Utils::docs_root() . '/storage/images/' . $file_name;

                $res = move_uploaded_file($file['tmp_name'], $destination);
                if (!$res) {
                    continue;
                }
                //$uploaded_images[] = $destination;
                $uploaded_images[] = $file_name;
            }
        }

        $single_file = "";
        if (isset($uploaded_images[0])) {
            $single_file = $uploaded_images[0];
        }


        return $is_single_file ? $single_file : $uploaded_images;
    }


    public static function create_thumbail($params = array())
    {

        ini_set('memory_limit', '-1');

        if (
            !isset($params['source']) ||
            !isset($params['target'])
        ) {
            return [];
        }



        if (!file_exists($params['source'])) {
            $img = url('assets/images/cow.jpeg');
            return $img;
        }


        $image = new Zebra_Image();

        $image->auto_handle_exif_orientation = true;
        $image->source_path = "" . $params['source'];
        $image->target_path = "" . $params['target'];


        if (isset($params['quality'])) {
            $image->jpeg_quality = $params['quality'];
        }

        $image->preserve_aspect_ratio = true;
        $image->enlarge_smaller_images = true;
        $image->preserve_time = true;
        $image->handle_exif_orientation_tag = true;

        $img_size = getimagesize($image->source_path); // returns an array that is filled with info





        $image->jpeg_quality = 50;
        if (isset($params['quality'])) {
            $image->jpeg_quality = $params['quality'];
        } else {
            $image->jpeg_quality = Utils::get_jpeg_quality(filesize($image->source_path));
        }
        if (!$image->resize(0, 0, ZEBRA_IMAGE_CROP_CENTER)) {
            return $image->source_path;
        } else {
            return $image->target_path;
        }
    }

    public static function get_jpeg_quality($_size)
    {
        $size = ($_size / 1000000);

        $qt = 50;
        if ($size > 5) {
            $qt = 10;
        } else if ($size > 4) {
            $qt = 10;
        } else if ($size > 2) {
            $qt = 10;
        } else if ($size > 1) {
            $qt = 11;
        } else if ($size > 0.8) {
            $qt = 11;
        } else if ($size > .5) {
            $qt = 12;
        } else {
            $qt = 15;
        }

        return $qt;
    }


    public static function system_boot()
    {

        Consultation::process_ongoing_consultations();
        Consultation::process_dosages();
        $u = Admin::user();
    }

    public static function start_session()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }



    public static function month($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('M - Y');
    }
    public static function my_day($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('d M');
    }


    public static function my_date_1($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('D - d M');
    }

    //return date in this formart 16th July 2021
    public static function my_date_2($t)
    {
        if ($t == null || strlen($t) < 5) {
            return $t;
        }
        $c = Carbon::parse($t);
        return $c->format('dS M Y');
    }


    public static function my_date($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('d M, Y');
    }

    public static function my_date_time_1($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        //return date and 24 hours format time
        return $c->format('d M, Y - H:m');
    }

    public static function my_date_time($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('d M, Y - h:m a');
    }

    public static function to_date_time($raw)
    {
        $t = Carbon::parse($raw);
        if ($t == null) {
            return  "-";
        }
        $my_t = $t->toDateString();

        return $my_t . " " . $t->toTimeString();
    }
    public static function number_format($num, $unit)
    {
        $num = (int)($num);
        $resp = number_format($num);
        if ($num < 2) {
            $resp .= " " . $unit;
        } else {
            $resp .= " " . Str::plural($unit);
        }
        return $resp;
    }





    public static function COUNTRIES()
    {
        $data = [];
        foreach (
            [
                '',
                "Uganda",
                "Somalia",
                "Nigeria",
                "Tanzania",
                "Kenya",
                "Sudan",
                "Rwanda",
                "Congo",
                "Afghanistan",
                "Albania",
                "Algeria",
                "American Samoa",
                "Andorra",
                "Angola",
                "Anguilla",
                "Antarctica",
                "Antigua and Barbuda",
                "Argentina",
                "Armenia",
                "Aruba",
                "Australia",
                "Austria",
                "Azerbaijan",
                "Bahamas",
                "Bahrain",
                "Bangladesh",
                "Barbados",
                "Belarus",
                "Belgium",
                "Belize",
                "Benin",
                "Bermuda",
                "Bhutan",
                "Bolivia",
                "Bosnia and Herzegovina",
                "Botswana",
                "Bouvet Island",
                "Brazil",
                "British Indian Ocean Territory",
                "Brunei Darussalam",
                "Bulgaria",
                "Burkina Faso",
                "Burundi",
                "Cambodia",
                "Cameroon",
                "Canada",
                "Cape Verde",
                "Cayman Islands",
                "Central African Republic",
                "Chad",
                "Chile",
                "China",
                "Christmas Island",
                "Cocos (Keeling Islands)",
                "Colombia",
                "Comoros",
                "Cook Islands",
                "Costa Rica",
                "Cote D'Ivoire (Ivory Coast)",
                "Croatia (Hrvatska",
                "Cuba",
                "Cyprus",
                "Czech Republic",
                "Denmark",
                "Djibouti",
                "Dominica",
                "Dominican Republic",
                "East Timor",
                "Ecuador",
                "Egypt",
                "El Salvador",
                "Equatorial Guinea",
                "Eritrea",
                "Estonia",
                "Ethiopia",
                "Falkland Islands (Malvinas)",
                "Faroe Islands",
                "Fiji",
                "Finland",
                "France",
                "France",
                "Metropolitan",
                "French Guiana",
                "French Polynesia",
                "French Southern Territories",
                "Gabon",
                "Gambia",
                "Georgia",
                "Germany",
                "Ghana",
                "Gibraltar",
                "Greece",
                "Greenland",
                "Grenada",
                "Guadeloupe",
                "Guam",
                "Guatemala",
                "Guinea",
                "Guinea-Bissau",
                "Guyana",
                "Haiti",
                "Heard and McDonald Islands",
                "Honduras",
                "Hong Kong",
                "Hungary",
                "Iceland",
                "India",
                "Indonesia",
                "Iran",
                "Iraq",
                "Ireland",
                "Israel",
                "Italy",
                "Jamaica",
                "Japan",
                "Jordan",
                "Kazakhstan",

                "Kiribati",
                "Korea (North)",
                "Korea (South)",
                "Kuwait",
                "Kyrgyzstan",
                "Laos",
                "Latvia",
                "Lebanon",
                "Lesotho",
                "Liberia",
                "Libya",
                "Liechtenstein",
                "Lithuania",
                "Luxembourg",
                "Macau",
                "Macedonia",
                "Madagascar",
                "Malawi",
                "Malaysia",
                "Maldives",
                "Mali",
                "Malta",
                "Marshall Islands",
                "Martinique",
                "Mauritania",
                "Mauritius",
                "Mayotte",
                "Mexico",
                "Micronesia",
                "Moldova",
                "Monaco",
                "Mongolia",
                "Montserrat",
                "Morocco",
                "Mozambique",
                "Myanmar",
                "Namibia",
                "Nauru",
                "Nepal",
                "Netherlands",
                "Netherlands Antilles",
                "New Caledonia",
                "New Zealand",
                "Nicaragua",
                "Niger",
                "Niue",
                "Norfolk Island",
                "Northern Mariana Islands",
                "Norway",
                "Oman",
                "Pakistan",
                "Palau",
                "Panama",
                "Papua New Guinea",
                "Paraguay",
                "Peru",
                "Philippines",
                "Pitcairn",
                "Poland",
                "Portugal",
                "Puerto Rico",
                "Qatar",
                "Reunion",
                "Romania",
                "Russian Federation",
                "Saint Kitts and Nevis",
                "Saint Lucia",
                "Saint Vincent and The Grenadines",
                "Samoa",
                "San Marino",
                "Sao Tome and Principe",
                "Saudi Arabia",
                "Senegal",
                "Seychelles",
                "Sierra Leone",
                "Singapore",
                "Slovak Republic",
                "Slovenia",
                "Solomon Islands",

                "South Africa",
                "S. Georgia and S. Sandwich Isls.",
                "Spain",
                "Sri Lanka",
                "St. Helena",
                "St. Pierre and Miquelon",
                "Suriname",
                "Svalbard and Jan Mayen Islands",
                "Swaziland",
                "Sweden",
                "Switzerland",
                "Syria",
                "Taiwan",
                "Tajikistan",
                "Thailand",
                "Togo",
                "Tokelau",
                "Tonga",
                "Trinidad and Tobago",
                "Tunisia",
                "Turkey",
                "Turkmenistan",
                "Turks and Caicos Islands",
                "Tuvalu",
                "Ukraine",
                "United Arab Emirates",
                "United Kingdom (Britain / UK)",
                "United States of America (USA)",
                "US Minor Outlying Islands",
                "Uruguay",
                "Uzbekistan",
                "Vanuatu",
                "Vatican City State (Holy See)",
                "Venezuela",
                "Viet Nam",
                "Virgin Islands (British)",
                "Virgin Islands (US)",
                "Wallis and Futuna Islands",
                "Western Sahara",
                "Yemen",
                "Yugoslavia",
                "Zaire",
                "Zambia",
                "Zimbabwe"
            ] as $key => $v
        ) {
            $data[$v] = $v;
        };
        return $data;
    }
}

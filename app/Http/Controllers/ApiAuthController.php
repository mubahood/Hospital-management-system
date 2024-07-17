<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiAuthController extends Controller
{

    use ApiResponser;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {

        /* $token = auth('api')->attempt([
            'username' => 'admin',
            'password' => 'admin',
        ]);
        die($token); */
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $query = auth('api')->user();
        return $this->success($query, $message = "Profile details", 200);
    }


    public function users()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('Account not found');
        }

        return $this->success(User::where([
            'company_id' => $u->company_id
        ])->get(), $message = "Success", 200);
    }

    public function projects()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('Account not found');
        }
        return $this->success(Project::where([
            'company_id' => $u->company_id
        ])
            ->get(), $message = "Success =>{$u->company_id}<=", 200);
    }

    public function tasks()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('Account not found');
        }
        return $this->success(Task::where([
            'assigned_to' => $u->id,
        ])
            ->orWhere([
                'manager_id' => $u->id,
            ])
            ->get(), $message = "Success", 200);
    }

    public function tasks_update_status(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('Account not found');
        }

        if ($r->task_id == null) {
            return $this->error('Task ID is required.');
        }


        $task = Task::find($r->task_id);
        if ($task == null) {
            return $this->error('Task not found. ' . $r->task_id);
        }
        if (strlen($r->delegate_submission_status) > 2) {
            $task->delegate_submission_status = $r->delegate_submission_status;
        }
        if (strlen($r->manager_submission_status) > 2) {
            $task->manager_submission_status = $r->manager_submission_status;
        }
        if (strlen($r->delegate_submission_remarks) > 2) {
            $task->delegate_submission_remarks = $r->delegate_submission_remarks;
        }
        if (strlen($r->manager_submission_remarks) > 2) {
            $task->manager_submission_remarks = $r->manager_submission_remarks;
        }

        try {
            $task->save();
        } catch (\Throwable $th) {
            return $this->error('Failed to update task.');
        }
        $task = Task::find($r->task_id);
        if ($task == null) {
            return $this->error('Task not found.');
        }

        return $this->success($task, $message = "Success", 200);
    }





    public function login(Request $r)
    {
        if ($r->username == null) {
            return $this->error('Username is required.');
        }

        if ($r->password == null) {
            return $this->error('Password is required.');
        }

        $r->username = trim($r->username);

        $u = User::where('phone_number_1', $r->username)
            ->orWhere('username', $r->username)
            ->orWhere('id', $r->username)
            ->orWhere('email', $r->username)
            ->first();


        if ($u == null) {
            $phone_number = Utils::prepare_phone_number($r->username);
            if (Utils::phone_number_is_valid($phone_number)) {
                $phone_number = $r->phone_number_1;
                $u = User::where('phone_number_1', $phone_number)
                    ->orWhere('username', $phone_number)
                    ->orWhere('email', $phone_number)
                    ->first();
            }
        }

        if ($u == null) {
            return $this->error('User account not found.');
        }
        if ($u->status == 3) {
            return $this->error('Account is deleted.');
        }



        JWTAuth::factory()->setTTL(60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'id' => $u->id,
            'password' => trim($r->password),
        ]);


        if ($token == null) {
            return $this->error('Wrong credentials.');
        }



        $u->token = $token;
        $u->remember_token = $token;

        return $this->success($u, 'Logged in successfully.');
    }


    public function register(Request $r)
    {
        if ($r->phone_number_1 == null) {
            return $this->error('Phone number is required.');
        }

        $phone_number = Utils::prepare_phone_number(trim($r->phone_number));


        if (!Utils::phone_number_is_valid($phone_number)) {
            return $this->error('Invalid phone number. ' . $phone_number);
        }

        if ($r->password == null) {
            return $this->error('Password is required.');
        }

        if ($r->name == null) {
            return $this->error('Name is required.');
        }





        $u = Administrator::where('phone_number_1', $phone_number)
            ->orWhere('username', $phone_number)->first();
        if ($u != null) {
            return $this->error('User with same phone number already exists.');
        }

        $user = new Administrator();

        $name = $r->name;

        $x = explode(' ', $name);

        if (
            isset($x[0]) &&
            isset($x[1])
        ) {
            $user->first_name = $x[0];
            $user->last_name = $x[1];
        } else {
            $user->first_name = $name;
        }

        $user->phone_number_1 = $phone_number;
        $user->username = $phone_number;
        $user->reg_number = $phone_number;
        $user->country = $phone_number;
        $user->occupation = $phone_number;
        $user->profile_photo_large = '';
        $user->location_lat = '';
        $user->location_long = '';
        $user->facebook = '';
        $user->twitter = '';
        $user->linkedin = '';
        $user->website = '';
        $user->other_link = '';
        $user->cv = '';
        $user->language = '';
        $user->about = '';
        $user->home_address = '';
        $user->name = $name;
        $user->password = password_hash(trim($r->password), PASSWORD_DEFAULT);
        if (!$user->save()) {
            return $this->error('Failed to create account. Please try again.');
        }

        $new_user = Administrator::find($user->id);
        if ($new_user == null) {
            return $this->error('Account created successfully but failed to log you in.');
        }
        Config::set('jwt.ttl', 60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'username' => $phone_number,
            'password' => trim($r->password),
        ]);

        $new_user->token = $token;
        $new_user->remember_token = $token;
        return $this->success($new_user, 'Account created successfully.');
    }



    public function tasks_create(Request $val)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'code' => 0,
                'message' => "User not found.",
            ]);
        }

        if ($val->assign_to_type != 'to_me') {
            if ($val->assigned_to == null) {
                return Utils::response([
                    'status' => 0,
                    'code' => 0,
                    'message' => "Assigned to is required.",
                ]);
            }
        }

        $message = "";
        $newTask = new Task();
        try {
            $task = new Task();
            $task->company_id = $u->id;
            $task->meeting_id = null;
            $task->assigned_to = $val->assigned_to;
            $task->project_id = $val->project_id;
            $task->created_by = $u->id;
            $task->name = $val->name;
            $task->task_description = $val->task_description;
            $task->due_to_date = Carbon::parse($val->due_to_date);
            $task->priority = 'Medium';
            $task->save();
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            return Utils::response([
                'status' => 0,
                'code' => 0,
                'message' => $message,
            ]);
        }

        $newTask = Task::find($task->id);

        return Utils::response([
            'status' => 1,
            'data' => $newTask,
            'code' => 1,
            'message' => 'Task created successfully.',
        ]);
    }


    public function meetings_create(Request $val)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'code' => 0,
                'message' => "User not found.",
            ]);
        }

        if (!(isset($val->resolutions)) || $val->resolutions == null) {
            //return resolutions not set
            return Utils::response([
                'status' => 0,
                'code' => 0,
                'message' => "Resolutions not set"
            ]);
        }

        $meeting = new Meeting();
        $meeting->created_by = $u->id;
        $meeting->company_id = $u->company_id;
        $meeting->name = $val->gps_latitude;
        $meeting->details = $val->details;
        $meeting->minutes_of_meeting = $val->details;
        $meeting->location = $val->location_text;
        $meeting->meeting_start_time = $val->start_date;
        $meeting->meeting_end_time = $val->end_date;
        $meeting->meeting_end_time = $val->session_date;
        $local_id = $val->id;
        $files = [];
        foreach (Image::where([
            'parent_id' => $local_id
        ])->get() as $key => $value) {
            $files[] = 'images/' . $value->src;
        }
        $meeting->attendance_list_pictures = $files;

        try {
            $meeting->save();
        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            return $this->error($msg);
        }


        $resolutions = null;
        try {
            $resolutions = json_decode($val->resolutions);
        } catch (\Throwable $th) {
            $resolutions = null;
        }


        if (($resolutions != null) && is_array($resolutions)) {
            foreach ($resolutions as $key => $res) {
                $task = new Task();
                $task->company_id = $u->id;
                $task->meeting_id = $meeting->id;
                $task->created_by = $u->id;
                $task->project_id = 1;
                $task->project_section_id = 1;
                $task->project_id = 1;
                $task->rate = 0;
                $task->hours = 0;
                $task->assigned_to = $res->assigned_to;
                $manager = Administrator::find($res->assigned_to);
                if ($manager != null) {
                    $task->manager_id = $manager->id;
                }
                $task->company_id = $u->company_id;
                $task->name = $res->name;
                $task->task_description = $res->task_description;
                $task->due_to_date = $res->due_to_date;
                $task->assign_to_type = $res->assign_to_type;
                $task->delegate_submission_status = 'Pending';
                $task->manager_submission_status = 'Pending';
                $task->is_submitted = 'Pending';
                $task->delegate_submission_remarks = '';
                $task->manager_submission_remarks = '';
                $task->priority = 'Medium';
                $task->save();
            }
        }

        $meeting = Meeting::find($meeting->id);
        return Utils::response([
            'status' => 1,
            'data' => $meeting,
            'code' => 1,
            'message' => 'Meeting created successfully.',
        ]);
    }



    public function password_change(Request $request)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $administrator_id = $u->id;

        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        if (
            $request->password == null ||
            strlen($request->password) < 2
        ) {
            return $this->error('Password is missing.');
        }

        //check if  current_password 
        if (
            $request->current_password == null ||
            strlen($request->current_password) < 2
        ) {
            return $this->error('Current password is missing.');
        }

        //check if  current_password
        if (
            !(password_verify($request->current_password, $u->password))
        ) {
            return $this->error('Current password is incorrect.');
        }

        $u->password = password_hash($request->password, PASSWORD_DEFAULT);
        $msg = "";
        $code = 1;
        try {
            $u->save();
            $msg = "Password changed successfully.";
            return $this->success($u, $msg, $code);
        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            $code = 0;
            return $this->error($msg);
        }
        return $this->success(null, $msg, $code);
    }


    public function delete_profile(Request $request)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $administrator_id = $u->id;

        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        $u->status = '3';
        $u->save();
        return $this->success(null, $message = "Deleted successfully!", 1);
    }


    public function update_profile(Request $request)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $administrator_id = $u->id;

        $u = Administrator::find($administrator_id);
        if ($u == null) {
            return $this->error('User not found.');
        }

        if (
            $request->first_name == null ||
            strlen($request->first_name) < 2
        ) {
            return $this->error('First name is missing.');
        }
        //validate all
        if (
            $request->last_name == null ||
            strlen($request->last_name) < 2
        ) {
            return $this->error('Last name is missing.');
        }



        if ($request->phone_number_1 != null && strlen($request->phone_number_1) > 4) {
            $anotherUser = Administrator::where([
                'phone_number_1' => $request->phone_number_1
            ])->first();
            if ($anotherUser != null) {
                if ($anotherUser->id != $u->id) {
                    return $this->error('Phone number is already taken.');
                }
            }

            $anotherUser = Administrator::where([
                'username' => $request->phone_number_1
            ])->first();
            if ($anotherUser != null) {
                if ($anotherUser->id != $u->id) {
                    return $this->error('Phone number is already taken.');
                }
            }

            $anotherUser = Administrator::where([
                'email' => $request->phone_number_1
            ])->first();
            if ($anotherUser != null) {
                if ($anotherUser->id != $u->id) {
                    return $this->error('Phone number is already taken.');
                }
            }
        }




        if ($request->email != null && strlen($request->email) > 4) {

            if (
                $request->email != null &&
                strlen($request->email) > 5
            ) {
                $anotherUser = Administrator::where([
                    'email' => $request->email
                ])->first();
                if ($anotherUser != null) {
                    if ($anotherUser->id != $u->id) {
                        return $this->error('Email is already taken.');
                    }
                }
                //check for username as well
                $anotherUser = Administrator::where([
                    'username' => $request->email
                ])->first();
                if ($anotherUser != null) {
                    if ($anotherUser->id != $u->id) {
                        return $this->error('Email is already taken.');
                    }
                }
                //validate email
                if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                    return $this->error('Invalid email address.');
                }
            }
        }



        $msg = "";
        //first letter to upper case
        $u->first_name = $request->first_name;

        //change first letter to upper case
        $u->first_name = ucfirst($u->first_name);


        $u->last_name = ucfirst($request->last_name);
        $u->phone_number_1 = $request->phone_number_1;
        $u->email = $request->email;
        $u->home_address = ucfirst($request->home_address);

        $images = [];
        if (!empty($_FILES)) {
            $images = Utils::upload_images_2($_FILES, false);
        }
        if (!empty($images)) {
            $u->avatar = 'images/' . $images[0];
        }

        $code = 1;
        try {
            $u->save();
            $u = Administrator::find($administrator_id);
            $msg = "Updated successfully.";
            return $this->success($u, $msg, $code);
        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            $code = 0;
            return $this->error($msg);
        }
        return $this->success(null, $msg, $code);
    }





    public function upload_media(Request $request)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'code' => 0,
                'message' => "User not found.",
            ]);
        }

        $administrator_id = $u->id;
        if (
            !isset($request->parent_id) ||
            $request->parent_id == null ||
            ((int)($request->parent_id)) < 1
        ) {
            return Utils::response([
                'status' => 0,
                'code' => 0,
                'message' => "Local parent ID is missing.",
            ]);
        }

        if (
            !isset($request->parent_endpoint) ||
            $request->parent_endpoint == null ||
            (strlen(($request->parent_endpoint))) < 3
        ) {
            return Utils::response([
                'status' => 0,
                'code' => 0,
                'message' => "Local parent ID endpoint is missing.",
            ]);
        }



        if (
            empty($_FILES)
        ) {
            return Utils::response([
                'status' => 0,
                'code' => 0,
                'message' => "Files not found.",
            ]);
        }


        $images = Utils::upload_images_2($_FILES, false);
        $_images = [];

        if (empty($images)) {
            return Utils::response([
                'status' => 0,
                'code' => 0,
                'message' => 'Failed to upload files.',
                'data' => null
            ]);
        }


        $msg = "";
        foreach ($images as $src) {

            if ($request->parent_endpoint == 'edit') {
                $img = Image::find($request->local_parent_id);
                if ($img) {
                    return Utils::response([
                        'status' => 0,
                        'code' => 0,
                        'message' => "Original photo not found",
                    ]);
                }
                $img->src =  $src;
                $img->thumbnail =  null;
                $img->save();
                return Utils::response([
                    'status' => 1,
                    'code' => 1,
                    'data' => json_encode($img),
                    'message' => "File updated.",
                ]);
            }


            $img = new Image();
            $img->administrator_id =  $administrator_id;
            $img->src =  $src;
            $img->thumbnail =  null;
            $img->parent_endpoint =  $request->parent_endpoint;
            $img->parent_id =  (int)($request->parent_id);
            $img->size = 0;
            $img->note = '';
            if (
                isset($request->note)
            ) {
                $img->note =  $request->note;
                $msg .= "Note not set. ";
            }



            $img->save();
            $_images[] = $img;
        }
        //Utils::process_images_in_backround();
        return Utils::response([
            'status' => 1,
            'code' => 1,
            'data' => json_encode($_POST),
            'message' => "File uploaded successfully.",
        ]);
    }
}

<?php

namespace App\Models;

use Encore\Admin\Form\Field\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as RelationsBelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Encore\Admin\Auth\Database\Administrator;


class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;



    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {

            $m->email = trim($m->email);
            if ($m->email != null && strlen($m->email) > 3) {
                if (!Utils::validateEmail($m->email)) {
                    // throw new \Exception("Invalid email address");
                } else {
                    //check if email exists
                    $u = User::where('email', $m->email)->first();
                    if ($u != null) {
                        throw new \Exception("Email already exists");
                    }
                    //check if username exists
                    $u = User::where('username', $m->email)->first();
                    if ($u != null) {
                        throw new \Exception("Email as Username already exists");
                    }
                }
            }

            $n = $m->first_name . " " . $m->last_name;
            if (strlen(trim($n)) > 1) {
                $m->name = trim($n);
            }
            $m->username = $m->email;
            if ($m->password == null || strlen($m->password) < 2) {
                $m->password = password_hash('4321', PASSWORD_DEFAULT);
            }

            $username = null;
            $phone = trim($m->phone_number_1);
            if (strlen($phone) > 2) {
                $phone = Utils::prepare_phone_number($phone);
                if (Utils::phone_number_is_valid($phone)) {
                    $username = $phone;
                    $m->phone_number_1 = $phone;
                    //check if username exists
                    $u = User::where('phone_number_1', $phone)->first();
                    if ($u != null) {
                        throw new \Exception("Phone number already exists");
                    }
                    //check if username exists
                    $u = User::where('phone_number_2', $phone)->first();
                    if ($u != null) {
                        throw new \Exception("Phone number already exists as username.");
                    }
                }
            }

            //check if $username is null or empty
            if ($username == null) {
                //check if email is valid and set it as username using var_filter
                $email = trim($m->email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $username = $email;
                }
            }
            //check if $username is null or empty
            if ($username == null || strlen($username) < 2) {
                throw new \Exception("Invalid username.");
            }

            //check if username exists
            $u = User::where('username', $username)->first();
            if ($u != null) {
                throw new \Exception("Username already exists");
            }
            $m->username = $username;

            //check if card_status is not set
            if ($m->card_status == null) {
                $m->card_status = "Inactive";
            }

            if ($m->card_status == "Active") {
                if ($m->card_number == null || strlen($m->card_number) < 2) {
                    $m->card_number = date('Y') . rand(100000, 999999) . rand(100000, 999999);
                    //check if card_number exists
                    $u = User::where('card_number', $m->card_number)->first();
                    if ($u != null) {
                        $m->card_number = date('Y') . rand(100000, 999999) . rand(100000, 999999);
                    }
                    //check if card_number exists
                    $u = User::where('card_number', $m->card_number)->first();
                    if ($u != null) {
                        $m->card_number = date('Y') . rand(100000, 999999) . rand(100000, 999999);
                    }
                    //check if card_number exists
                    $u = User::where('card_number', $m->card_number)->first();
                    if ($u != null) {
                        throw new \Exception("Card number already exists.");
                    }
                }

                if ($m->card_expiry == null || strlen($m->card_expiry) < 2) {
                    $m->card_expiry = date('Y-m-d', strtotime('+2 year'));
                }
                //card_accepts_credit
                if ($m->card_accepts_credit == null) {
                    $m->card_accepts_credit = 'No';
                }

                //card_max_credit
                if ($m->card_max_credit == null) {
                    $m->card_max_credit = 0;
                }
                //is_dependent
                if ($m->is_dependent == null) {
                    $m->is_dependent = 'No';
                }
                //dependent_status
                if ($m->dependent_status == null) {
                    $m->dependent_status = 'Inactive';
                }
                if ($m->is_dependent == 'Yes') {
                    $u = User::find($m->dependent_id);
                    if ($u == null) {
                        throw new \Exception("Dependent not found.");
                    }
                }
                //card_expiry
                if ($m->card_expiry == null) {
                    $m->card_expiry = date('Y-m-d', strtotime('+2 year'));
                }
                //belongs_to_company_status
                if ($m->belongs_to_company_status == null) {
                    $m->belongs_to_company_status = 'Inactive';
                }
            }
        });


        self::updating(function ($m) {

            $m->email = trim($m->email);
            if ($m->email != null && strlen($m->email) > 3) {
                if (!Utils::validateEmail($m->email)) {
                    // throw new \Exception("Invalid email address");
                } else {
                    //check if email exists
                    $u = User::where('email', $m->email)->first();
                    if ($u != null && $u->id != $m->id) {
                        throw new \Exception("Email already exists");
                    }
                    //check if username exists
                    $u = User::where('username', $m->email)->first();
                    if ($u != null && $u->id != $m->id) {
                        throw new \Exception("Email as Username already exists");
                    }
                }
            }

            $n = $m->first_name . " " . $m->last_name;
            if (strlen(trim($n)) > 1) {
                $m->name = trim($n);
            }
            $m->username = $m->email;
            if ($m->password == null || strlen($m->password) < 2) {
                $m->password = password_hash('4321', PASSWORD_DEFAULT);
            }

            $username = null;
            $phone = trim($m->phone_number_1);
            if (strlen($phone) > 2) {
                $phone = Utils::prepare_phone_number($phone);
                if (Utils::phone_number_is_valid($phone)) {
                    $username = $phone;
                    $m->phone_number_1 = $phone;
                    //check if username exists
                    $u = User::where('phone_number_1', $phone)->first();
                    if ($u != null && $u->id != $m->id) {
                        throw new \Exception("Phone number already exists");
                    }
                    //check if username exists
                    $u = User::where('phone_number_2', $phone)->first();
                    if ($u != null) {
                        throw new \Exception("Phone number already exists as username.");
                    }
                }
            }

            //check if $username is null or empty
            if ($username == null) {
                //check if email is valid and set it as username using var_filter
                $email = trim($m->email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $username = $email;
                }
            }
            //check if $username is null or empty
            if ($username == null || strlen($username) < 2) {
                throw new \Exception("Invalid username.");
            }

            //check if username exists
            $u = User::where('username', $username)->first();
            if ($u != null && $u->id != $m->id) {
                throw new \Exception("Username already exists");
            }
            $m->username = $username;

            //check if card_status is not set
            if ($m->card_status == null) {
                $m->card_status = "Inactive";
            }

            if ($m->card_status == "Active") {
                if ($m->card_number == null || strlen($m->card_number) < 2) {
                    $m->card_number = date('Y') . rand(100000, 999999) . rand(100000, 999999);
                    //check if card_number exists
                    $u = User::where('card_number', $m->card_number)->first();
                    if ($u != null && $u->id != $m->id) {
                        $m->card_number = date('Y') . rand(100000, 999999) . rand(100000, 999999);
                    }
                    //check if card_number exists
                    $u = User::where('card_number', $m->card_number)->first();
                    if ($u != null && $u->id != $m->id) {
                        $m->card_number = date('Y') . rand(100000, 999999) . rand(100000, 999999);
                    }
                    //check if card_number exists
                    $u = User::where('card_number', $m->card_number)->first();
                    if ($u != null && $u->id != $m->id) {
                        throw new \Exception("Card number already exists.");
                    }
                }

                if ($m->card_expiry == null || strlen($m->card_expiry) < 2) {
                    $m->card_expiry = date('Y-m-d', strtotime('+2 year'));
                }
                //card_accepts_credit
                if ($m->card_accepts_credit == null) {
                    $m->card_accepts_credit = 'No';
                }

                //card_max_credit
                if ($m->card_max_credit == null) {
                    $m->card_max_credit = 0;
                }
                //is_dependent
                if ($m->is_dependent == null) {
                    $m->is_dependent = 'No';
                }
                //dependent_status
                if ($m->dependent_status == null) {
                    $m->dependent_status = 'Inactive';
                }
                if ($m->is_dependent == 'Yes') {
                    $u = User::find($m->dependent_id);
                    if ($u == null) {
                        throw new \Exception("Dependent not found.");
                    }
                }
                //card_expiry
                if ($m->card_expiry == null) {
                    $m->card_expiry = date('Y-m-d', strtotime('+2 year'));
                }
                //belongs_to_company_status
                if ($m->belongs_to_company_status == null) {
                    $m->belongs_to_company_status = 'Inactive';
                }
            }
        });
    }



    public function send_password_reset()
    {
        $u = $this;
        $u->stream_id = rand(100000, 999999);
        $u->save();
        $data['email'] = $u->email;
        $data['name'] = $u->name;
        $data['subject'] = env('APP_NAME') . " - Password Reset";
        $data['body'] = "<br>Dear " . $u->name . ",<br>";
        $data['body'] .= "<br>Please click the link below to reset your " . env('APP_NAME') . " System password.<br><br>";
        $data['body'] .= url('reset-password') . "?token=" . $u->stream_id . "<br>";
        $data['body'] .= "<br>Thank you.<br><br>";
        $data['body'] .= "<br><small>This is an automated message, please do not reply.</small><br>";
        $data['view'] = 'mail-1';
        $data['data'] = $data['body'];
        try {
            Utils::mail_sender($data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public static function update_rating($id)
    {
        $user = User::find($id);
        /* $tasks = Task::where('assigned_to', $id)->get();
        $rate = 0;
        $count = 0;
        foreach ($tasks as $task) {
            if ($task->manager_submission_status != 'Not Submitted') {
                $rate += $task->rate;
                $count++;
            }
        }
        if ($count > 0) {
            $rate = $rate / $count;
        } */
        $work_load_pending = Task::where('assigned_to', $id)->where('manager_submission_status', 'Not Submitted')
            ->sum('hours');
        $work_load_completed = Task::where('assigned_to', $id)->where('manager_submission_status', 'Done')
            ->sum('hours');
        $user->work_load_pending = $work_load_pending;
        $user->work_load_completed = $work_load_completed;
        $user->save();
    }


    protected $table = "admin_users";

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }


    //appends
    protected $appends = ['short_name'];

    public function getShortNameAttribute()
    {
        //in this formart - J. Doe from first_name and last_name
        if (strlen($this->first_name) > 1) {
            $letter_1 = substr($this->first_name, 0, 1);
        } else {
            $letter_1 = $this->first_name;
        }
        return $letter_1 . ". " . $this->last_name;
    }

    //get doctors list

    public static function get_doctors()
    {
        $users = [];
        foreach (
            User::where('company_id', 1)
                ->orderBy('name', 'asc')
                ->get() as $key => $value
        ) {
            $users[$value->id] = $value->name;
        }
        return $users;
    }

    //get card
    public function get_card()
    {
        if ($this->is_dependent == 'Yes') {
            $c = User::find($this->dependent_id);
            return $c;
        } else {
            return $this;
        }
    }
}

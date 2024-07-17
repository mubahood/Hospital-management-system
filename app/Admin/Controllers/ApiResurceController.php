<?php

namespace App\Http\Controllers;

use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ApiResurceController extends Controller
{

    use ApiResponser;
    public function index(Request $r, $model)
    {

        $className = "App\Models\\" . $model;
        $obj = new $className;

        if (isset($_POST['_method'])) {
            unset($_POST['_method']);
        }
        if (isset($_GET['_method'])) {
            unset($_GET['_method']);
        }


        $conditions = [];
        foreach ($_GET as $k => $v) {
            if (substr($k, 0, 2) == 'q_') {
                $conditions[substr($k, 2, strlen($k))] = trim($v);
            }
        }
        $is_private = true;
        if (isset($_GET['is_not_private'])) {
            $is_not_private = ((int)($_GET['is_not_private']));
            if ($is_not_private == 1) {
                $is_private = false;
            }
        }
        if ($is_private) {
            $u = $r->user;
            $administrator_id = $u->id;
            if ($u == null) {
                return $this->error('User not found.');
            }
            $conditions['administrator_id'] = $administrator_id;
        }

        $items = [];
        $msg = "";

        try {
            $items = $className::where($conditions)->get();
            $msg = "Success";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }

        if ($success) {
            return $this->success($items, 'Success');
        } else {
            return $this->error($msg);
        }
    }





    public function delete(Request $r, $model)
    {
        $administrator_id = Utils::get_user_id($r);
        $u = Administrator::find($administrator_id);


        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }


        $className = "App\Models\\" . $model;
        $id = ((int)($r->online_id));
        $obj = $className::find($id);


        if ($obj == null) {
            return Utils::response([
                'status' => 0,
                'message' => "Item already deleted.",
            ]);
        }


        try {
            $obj->delete();
            $msg = "Deleted successfully.";
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }


        if ($success) {
            return Utils::response([
                'status' => 1,
                'data' => $obj,
                'message' => $msg
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => $msg
            ]);
        }
    }


    public function update(Request $r, $model)
    {

        $u = auth('api')->user();
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => "User not found.",
            ]);
        }

        $className = "App\Models\\" . $model;
        $id = ((int)($r->id));
        $obj = $className::find($id);

        $isEdit = true;
        if ($obj == null) {
            $obj = new $className;
            $isEdit = false;
        }
        if ($isEdit) {
            if (isset($r->my_task)) {
                if ($r->my_task == 'delete') {
                    $obj->delete();
                    return Utils::response([
                        'status' => 1,
                        'message' => "Deleted successfully.",
                    ]);
                }
            }
        }

        $table_name = $obj->getTable();
        $cols = Schema::getColumnListing($table_name);



        if (isset($_POST['online_id'])) {
            unset($_POST['online_id']);
        }

        $except = [
            'created_at',
            'updated_at',
            'deleted_at',
            'online_id',
            'id',
            'administrator_id',
            'user_id',
            'created_by',
            'updated_by',
        ];

        foreach ($_POST as $key => $value) {
            if (in_array($key, $except)) {
                continue;
            }
            if (!in_array($key, $cols)) {
                continue;
            }
            $obj->$key = $value;
        }

        $success = false;
        $msg = "";
        if ($isEdit) {
            $msg = "Updated successfully.";
        } else {
            $msg = "Created successfully.";
        }
        try {
            $obj->save();
            $success = true;
        } catch (Exception $e) {
            $success = false;
            $msg = $e->getMessage();
        }


        if ($success) {
            return Utils::response([
                'status' => 1,
                'data' => $obj,
                'message' => $msg
            ]);
        } else {
            return Utils::response([
                'status' => 0,
                'data' => null,
                'message' => $msg
            ]);
        }
    }
}

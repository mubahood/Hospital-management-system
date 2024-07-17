<?php

namespace App\Admin\Controllers;

use App\Admin\Forms\Setting;
use App\Admin\Forms\Steps\Info;
use App\Admin\Forms\Steps\Password;
use App\Admin\Forms\Steps\Profile;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\MultipleSteps;

class UserController extends Controller
{

 

    public function index(Content $content)
    {


        $steps = [
            'info'     => Info::class,
            'profile'     => Profile::class,
            'password' => Password::class,

        ];

        return $content
            ->title('Register')
            ->body(MultipleSteps::make($steps));


        return $content;
    }
}

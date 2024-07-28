<?php

namespace App\Admin\Actions\Consultation;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class AddBillAction extends RowAction
{
    public $name = 'Update Invoice';

    public function handle(Model $model)
    {
        return $this->response()->redirect(admin_url("/consultation-billing/{$model->id}/edit"));
    }
}


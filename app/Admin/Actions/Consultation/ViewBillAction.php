<?php

namespace App\Admin\Actions\Consultation;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;


class ViewBillAction extends RowAction
{
    public $name = 'View Invoice';

    public function handle(Model $model)
    {
        return $this->response()->redirect(url("/regenerate-invoice?id={$model->id}"));
    }
}

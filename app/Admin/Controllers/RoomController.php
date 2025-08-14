<?php

namespace App\Admin\Controllers;

use App\Models\Room;
use App\Models\Department;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RoomController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Rooms';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Room());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Room Name'));
        $grid->column('room_number', __('Room Number'));
        $grid->column('department.name', __('Department'));
        $grid->column('room_type', __('Type'))->label([
            'consultation' => 'primary',
            'surgery' => 'danger',
            'emergency' => 'warning',
            'general' => 'info',
            'icu' => 'success'
        ]);
        $grid->column('capacity', __('Capacity'));
        $grid->column('is_active', __('Active'))->display(function ($is_active) {
            return $is_active ? 'Yes' : 'No';
        })->label([
            1 => 'success',
            0 => 'danger',
        ]);
        $grid->column('created_at', __('Created At'))->display(function ($created_at) {
            return date('Y-m-d H:i', strtotime($created_at));
        });

        $grid->filter(function($filter) {
            $filter->like('name', 'Room Name');
            $filter->like('room_number', 'Room Number');
            $filter->equal('department_id', 'Department')->select(Department::pluck('name', 'id'));
            $filter->equal('room_type', 'Type')->select([
                'consultation' => 'Consultation',
                'surgery' => 'Surgery',
                'emergency' => 'Emergency',
                'general' => 'General',
                'icu' => 'ICU'
            ]);
            $filter->equal('is_active', 'Active')->select([0 => 'No', 1 => 'Yes']);
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Room::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('name', __('Room Name'));
        $show->field('room_number', __('Room Number'));
        $show->field('department.name', __('Department'));
        $show->field('room_type', __('Type'));
        $show->field('capacity', __('Capacity'));
        $show->field('is_active', __('Active'))->as(function ($is_active) {
            return $is_active ? 'Yes' : 'No';
        });
        $show->field('description', __('Description'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Room());

        $form->text('name', __('Room Name'))
            ->required()
            ->placeholder('e.g., Consultation Room A');
            
        $form->text('room_number', __('Room Number'))
            ->required()
            ->placeholder('e.g., R001');
            
        $form->select('department_id', __('Department'))
            ->options(Department::pluck('name', 'id'))
            ->required();
            
        $form->select('room_type', __('Room Type'))
            ->options([
                'consultation' => 'Consultation',
                'surgery' => 'Surgery',
                'emergency' => 'Emergency',
                'general' => 'General',
                'icu' => 'ICU'
            ])
            ->required();
            
        $form->number('capacity', __('Capacity'))
            ->min(1)
            ->default(1)
            ->required();
            
        $form->switch('is_active', __('Active'))
            ->default(1);
            
        $form->textarea('description', __('Description'))
            ->placeholder('Optional room description...');

        return $form;
    }
}

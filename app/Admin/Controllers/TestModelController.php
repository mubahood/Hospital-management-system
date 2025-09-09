<?php

namespace App\Admin\Controllers;

use App\Models\TestModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TestModelController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'TestModel';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TestModel());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('name', __('Name'));
        $grid->column('code', __('Code'));
        $grid->column('description', __('Description'));
        $grid->column('short_note', __('Short note'));
        $grid->column('medium_content', __('Medium content'));
        $grid->column('long_content', __('Long content'));
        $grid->column('quantity', __('Quantity'));
        $grid->column('status', __('Status'));
        $grid->column('small_number', __('Small number'));
        $grid->column('medium_number', __('Medium number'));
        $grid->column('big_number', __('Big number'));
        $grid->column('positive_number', __('Positive number'));
        $grid->column('price', __('Price'));
        $grid->column('rating', __('Rating'));
        $grid->column('coordinates', __('Coordinates'));
        $grid->column('is_active', __('Is active'));
        $grid->column('birth_date', __('Birth date'));
        $grid->column('start_time', __('Start time'));
        $grid->column('created_at_custom', __('Created at custom'));
        $grid->column('last_login', __('Last login'));
        $grid->column('birth_year', __('Birth year'));
        $grid->column('metadata', __('Metadata'));
        $grid->column('settings', __('Settings'));
        $grid->column('file_data', __('File data'));
        $grid->column('gender', __('Gender'));
        $grid->column('hobbies', __('Hobbies'));
        $grid->column('unique_id', __('Unique id'));
        $grid->column('ip_address', __('Ip address'));
        $grid->column('mac_address', __('Mac address'));
        $grid->column('morphable_type', __('Morphable type'));
        $grid->column('morphable_id', __('Morphable id'));
        $grid->column('uuid_morphable_type', __('Uuid morphable type'));
        $grid->column('uuid_morphable_id', __('Uuid morphable id'));
        $grid->column('user_id', __('User id'));
        $grid->column('category_id', __('Category id'));
        $grid->column('location', __('Location'));
        $grid->column('coordinates_point', __('Coordinates point'));
        $grid->column('path', __('Path'));
        $grid->column('area', __('Area'));
        $grid->column('images_list', __('Images list'));
        $grid->column('tags', __('Tags'));
        $grid->column('ratings', __('Ratings'));
        $grid->column('comments', __('Comments'));
        $grid->column('documents', __('Documents'));
        $grid->column('videos', __('Videos'));
        $grid->column('audios', __('Audios'));

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
        $show = new Show(TestModel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('code', __('Code'));
        $show->field('description', __('Description'));
        $show->field('short_note', __('Short note'));
        $show->field('medium_content', __('Medium content'));
        $show->field('long_content', __('Long content'));
        $show->field('quantity', __('Quantity'));
        $show->field('status', __('Status'));
        $show->field('small_number', __('Small number'));
        $show->field('medium_number', __('Medium number'));
        $show->field('big_number', __('Big number'));
        $show->field('positive_number', __('Positive number'));
        $show->field('price', __('Price'));
        $show->field('rating', __('Rating'));
        $show->field('coordinates', __('Coordinates'));
        $show->field('is_active', __('Is active'));
        $show->field('birth_date', __('Birth date'));
        $show->field('start_time', __('Start time'));
        $show->field('created_at_custom', __('Created at custom'));
        $show->field('last_login', __('Last login'));
        $show->field('birth_year', __('Birth year'));
        $show->field('metadata', __('Metadata'));
        $show->field('settings', __('Settings'));
        $show->field('file_data', __('File data'));
        $show->field('gender', __('Gender'));
        $show->field('hobbies', __('Hobbies'));
        $show->field('unique_id', __('Unique id'));
        $show->field('ip_address', __('Ip address'));
        $show->field('mac_address', __('Mac address'));
        $show->field('morphable_type', __('Morphable type'));
        $show->field('morphable_id', __('Morphable id'));
        $show->field('uuid_morphable_type', __('Uuid morphable type'));
        $show->field('uuid_morphable_id', __('Uuid morphable id'));
        $show->field('user_id', __('User id'));
        $show->field('category_id', __('Category id'));
        $show->field('location', __('Location'));
        $show->field('coordinates_point', __('Coordinates point'));
        $show->field('path', __('Path'));
        $show->field('area', __('Area'));
        $show->field('images_list', __('Images list'));
        $show->field('tags', __('Tags'));
        $show->field('ratings', __('Ratings'));
        $show->field('comments', __('Comments'));
        $show->field('documents', __('Documents'));
        $show->field('videos', __('Videos'));
        $show->field('audios', __('Audios'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TestModel());

        $form->textarea('name', __('Name'));
        $form->textarea('code', __('Code'));
        $form->textarea('description', __('Description'));
        $form->textarea('short_note', __('Short note'));
        $form->textarea('medium_content', __('Medium content'));
        $form->textarea('long_content', __('Long content'));
        $form->textarea('quantity', __('Quantity'));
        $form->textarea('status', __('Status'));
        $form->textarea('small_number', __('Small number'));
        $form->textarea('medium_number', __('Medium number'));
        $form->textarea('big_number', __('Big number'));
        $form->textarea('positive_number', __('Positive number'));
        $form->textarea('price', __('Price'));
        $form->textarea('rating', __('Rating'));
        $form->textarea('coordinates', __('Coordinates'));
        $form->textarea('is_active', __('Is active'));
        $form->textarea('birth_date', __('Birth date'));
        $form->textarea('start_time', __('Start time'));
        $form->textarea('created_at_custom', __('Created at custom'));
        $form->textarea('last_login', __('Last login'));
        $form->textarea('birth_year', __('Birth year'));
        $form->textarea('metadata', __('Metadata'));
        $form->textarea('settings', __('Settings'));
        $form->textarea('file_data', __('File data'));
        $form->textarea('gender', __('Gender'));
        $form->textarea('hobbies', __('Hobbies'));
        $form->textarea('unique_id', __('Unique id'));
        $form->textarea('ip_address', __('Ip address'));
        $form->textarea('mac_address', __('Mac address'));
        $form->textarea('morphable_type', __('Morphable type'));
        $form->textarea('morphable_id', __('Morphable id'));
        $form->textarea('uuid_morphable_type', __('Uuid morphable type'));
        $form->textarea('uuid_morphable_id', __('Uuid morphable id'));
        $form->textarea('user_id', __('User id'));
        $form->textarea('category_id', __('Category id'));
        $form->textarea('location', __('Location'));
        $form->textarea('coordinates_point', __('Coordinates point'));
        $form->textarea('path', __('Path'));
        $form->textarea('area', __('Area'));
        $form->textarea('images_list', __('Images list'));
        $form->textarea('tags', __('Tags'));
        $form->textarea('ratings', __('Ratings'));
        $form->textarea('comments', __('Comments'));
        $form->textarea('documents', __('Documents'));
        $form->textarea('videos', __('Videos'));
        $form->textarea('audios', __('Audios'));

        return $form;
    }
}

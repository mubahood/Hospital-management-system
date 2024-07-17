<?php

namespace App\Admin\Controllers;

use App\Models\Project;
use App\Models\ProjectSection;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProjectSectionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Project Deliverables';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ProjectSection());
        $grid->model()->where('company_id', auth()->user()->company_id);
        $grid->disableBatchActions();
        $grid->disableCreation();
        $grid->disableExport();


        $grid->column('name', __('Deliverable'))->sortable();
        $grid->column('project_id', __('Project'))
            ->display(function ($project_id) {
                $project = Project::find($project_id);
                if ($project == null) {
                    return "Project not found";
                }
                return $project->name;
            })
            ->sortable();

        $grid->column('section_description', __('Description'))->hide();
        $grid->column('progress', __('Progress'))
            ->display(function ($progress) {
                return $progress . '%';
            })
            ->progressBar($style = 'primary', $size = 'sm', $max = 100)
            ->sortable()
            ->help('Progress of the deliverable')
            ->totalRow(function ($amount) {
                $count = ProjectSection::where('company_id', auth()->user()->company_id)->count();
                if ($count == 0) {
                    return "<b class='text-danger'>Total progress: 0%</b>";
                }
                $amount = $amount / $count;
                $amount = round($amount, 2);
                if ($amount < 50) {
                    return "<b class='text-danger'>Total progress: $amount%</b>";
                } else {
                    return "<b class='text-success'>Total progress: $amount%</b>";
                }
            });
        $grid->column('section_progress', __('Section progress'));

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
        $show = new Show(ProjectSection::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('company_id', __('Company id'));
        $show->field('project_id', __('Project id'));
        $show->field('name', __('Name'));
        $show->field('section_description', __('Section description'));
        $show->field('progress', __('Progress'));
        $show->field('section_progress', __('Section progress'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ProjectSection());

        $form->number('company_id', __('Company id'));
        $form->number('project_id', __('Project id'));
        $form->textarea('name', __('Name'));
        $form->textarea('section_description', __('Section description'));
        $form->number('progress', __('Progress'));
        $form->textarea('section_progress', __('Section progress'));

        return $form;
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\Client;
use App\Models\Project;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProjectController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Projects';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Project());




        $grid->model()->where('company_id', auth()->user()->company_id);
        $u = auth()->user();

        if (!$u->isRole('company-admin')) {
            if (!$u->can('administrator')) {
                $grid->model()->where('administrator_id', auth()->user()->id);
            }
            //disable delete action
            $grid->actions(function ($actions) {
                $actions->disableDelete();
            });
        }


        $grid->disableBatchActions();
        $grid->quickSearch('name')->placeholder('Search by name');
        $grid->column('id', __('ID'))->sortable()->hide();


        $grid->column('logo', __('Logo'))
            ->lightbox(['width' => 60, 'height' => 60])
            ->sortable();
        $grid->column('name', __('Project Name'))->sortable()->hide();

        $grid->column('short_name', __('Project'))->sortable();

        $grid->column('client_id', __('Client'))
            ->display(function ($client_id) {
                $client = Client::find($client_id);
                if ($client == null) {
                    return "Client not found";
                }
                return $client->name;
            })
            ->sortable();
        $grid->column('administrator_id', __('Project Manager'))
            ->display(function ($client_id) {
                $client = Administrator::find($client_id);
                if ($client == null) {
                    return "Manager not found";
                }
                return $client->name;
            })
            ->sortable();

        $grid->column('other_clients', __('Other clients'))->hide();
        $grid->column('status', __('Status'))->sortable()
            ->label([
                'Active' => 'primary',
                'Completed' => 'success',
                'On-hold' => 'warning',
                'Cancelled' => 'danger',
            ], 'danger');

        $grid->column('details', __('Details'))->hide();
        $grid->column('deliverables', __('Deliverables'))
            ->display(function ($deliverables) {
                return count($this->project_sections);
            })
            ->sortable();
        $grid->column('progress', __('Progress'))->sortable()
            ->progressBar($style = 'primary', $size = 'sm', $max = 100)
            ->totalRow(function ($amount) {
                //to $amount percentage
                $u = Admin::user();
                $progress = Project::where([
                    'company_id' => $u->company_id,
                    'status' => 'Active',
                ])->sum('progress');
                $amount = $amount / Project::where([
                    'company_id' => $u->company_id,
                    'status' => 'Active',
                ])->count();
                
                $amount = round($amount, 2);
                if ($amount < 50) {
                    return "<b class='text-danger'>Total progress: $amount%</b>";
                } else {
                    return "<b class='text-success'>Total progress: $amount%</b>";
                }
            });

        $grid->column('created_at', __('Created'))
            ->display(function ($created_at) {
                return date('d-m-Y', strtotime($created_at));
            })->hide();

        $grid->column('start_date', __('Start Date'))
            ->display(function ($start_date) {
                return date('d-m-Y', strtotime($start_date));
            })->hide();
        $grid->column('end_date', __('End Date'))
            ->display(function ($end_date) {
                return date('d-m-Y', strtotime($end_date));
            })->sortable();
        $grid->column('print', __('Repoert'))
            ->display(function ($created_at) {
                $url = url('project-report?id=' . $this->id);
                return "<a href='{$url}' target='_blank' class='btn btn-sm btn-primary'>Generate Report</a>";
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
        $show = new Show(Project::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('company_id', __('Company id'));
        $show->field('client_id', __('Client id'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('name', __('Name'));
        $show->field('short_name', __('Short name'));
        $show->field('logo', __('Logo'));
        $show->field('other_clients', __('Other clients'));
        $show->field('details', __('Details'));
        $show->field('progress', __('Progress'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Project());

        $form->tab('Basic Information', function ($form) {
            
            $clients = \App\Models\Client::where('company_id', auth()->user()->company_id)
                ->orderBy('name')
                ->pluck('name', 'id');
            $form->text('name', __('Project Name'))->rules('required');
            $form->text('short_name', __('Project Short name'))->rules('required');
            $form->hidden('company_id')->value(auth()->user()->company_id);
            $form->select('client_id', __('Project Client'))
                ->options($clients)
                ->rules('required');

            $form->date('start_date', __('Project Start Date'))->rules('required');
            $form->date('end_date', __('Project End Date'))->rules('required');
            $form->multipleSelect('other_clients', "Other Clients")
                ->options($clients);

            $employees = Administrator::where('company_id', auth()->user()->company_id)
                ->orderBy('name')
                ->pluck('name', 'id');
            $form->select('administrator_id', __('Project Manager'))
                ->options($employees)
                ->rules('required');

            $form->image('logo', __('Project Icon (Logo)'));

            $form->quill('details', __('Project Details'));

            $form->divider();
            $form->text('budget_overview', __('Budget overview'));
            $form->text('schedule_overview', __('Scheduled overview'));
            $form->text('risks_issues', __('Project Risks & Issues'));
            $form->text('concerns_recommendations', __('Concerns and Recommendations'));

            $form->radio('status', __('Project Status'))
                ->options([
                    'Active' => 'Active',
                    'Completed' => 'Completed',
                    'On-hold' => 'On Hold',
                    'Cancelled' => 'Cancelled',
                ])
                ->default('active')
                ->rules('required');


            if ($form->isCreating()) {
                $form->hidden('progress', __('Progress'))->default(0);
            }
        })->tab('Project Sections', function ($form) {
            $form->hasMany('project_sections', 'Project Deliverables', function (Form\NestedForm $form) {
                $form->text('name', __('Deliverable Name'))->rules('required');
                $form->textarea('section_description', __('Deliverable Description'));
                $form->text('progress', 'Deliverable Progress Percentage (out of 100%)')
                    ->rules([
                        'required',
                        'min:0',
                        'max:100',
                    ])
                    ->attribute('type', 'number')
                    ->attribute('min', 0)
                    ->attribute('max', 100)
                    ->attribute('style', 'width: 100px; height: 30px;')
                    ->default(0);
                $form->textarea('section_progress', __('Progress Description'));
                $form->hidden('company_id')->value(auth()->user()->company_id);
            });
        });
        return $form;
    }
}
/* 
->tab('Project Tasks', function ($form) {
            $form->hasMany('tasks', 'Project Tasks', function (Form\NestedForm $form) {
                $form->text('name', __('Task Name'))->rules('required');
                $form->textarea('task_description', __('Task Description'));
                $form->hidden('company_id')->value(auth()->user()->company_id);
                $form->hidden('progress')->default(0);
            });
        })->tab('Project Milestones', function ($form) {
            $form->hasMany('milestones', 'Project Milestones', function (Form\NestedForm $form) {
                $form->text('name', __('Milestone Name'))->rules('required');
                $form->textarea('milestone_description', __('Milestone Description'));
                $form->hidden('company_id')->value(auth()->user()->company_id);
                $form->hidden('progress')->default(0);
            });
        })->tab('Project Deliverables', function ($form) {
            $form->hasMany('deliverables', 'Project Deliverables', function (Form\NestedForm $form) {
                $form->text('name', __('Deliverable Name'))->rules('required');
                $form->textarea('deliverable_description', __('Deliverable Description'));
                $form->hidden('company_id')->value(auth()->user()->company_id);
                $form->hidden('progress')->default(0);
            });
        })->tab('Project Invoices', function ($form) {
            $form->hasMany('invoices', 'Project Invoices', function (Form\NestedForm $form) {
                $form->text('name', __('Invoice Name'))->rules('required');
                $form->textarea('invoice_description', __('Invoice Description'));
                $form->hidden('company_id')->value(auth()->user()->company_id);
                $form->hidden('progress')->default(0);
            });
        })->tab('Project Payments', function ($form) {
            $form->hasMany('payments',  'Project Payments', function (Form\NestedForm $form) {
                $form->text('name', __('Payment Name'))->rules('required');
                $form->textarea('payment_description', __('Payment Description'));
                $form->hidden('company_id')->value(auth()->user()->company_id);
                $form->hidden('progress')->default(0);
            }); 
        });
*/

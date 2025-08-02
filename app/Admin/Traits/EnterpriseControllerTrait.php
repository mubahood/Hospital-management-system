<?php

namespace App\Admin\Traits;

use App\Models\Enterprise;
use App\Models\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Support\Facades\Log;

trait EnterpriseControllerTrait
{
    /**
     * Get the current user's enterprise
     */
    protected function getCurrentEnterprise()
    {
        $user = Admin::user();
        if (!$user || !$user->enterprise_id) {
            return null;
        }
        
        return Enterprise::find($user->enterprise_id);
    }

    /**
     * Validate and get current enterprise, redirect if invalid
     */
    protected function validateEnterprise()
    {
        $enterprise = $this->getCurrentEnterprise();
        
        if (!$enterprise) {
            admin_error('No enterprise found. Please contact your system administrator.');
            return redirect(admin_url('/'));
        }
        
        return $enterprise;
    }

    /**
     * Apply enterprise scoping to grid models
     */
    protected function applyEnterpriseScope(Grid $grid, $enterpriseId = null)
    {
        $enterpriseId = $enterpriseId ?: Admin::user()->enterprise_id;
        
        if ($enterpriseId) {
            $grid->model()->where('enterprise_id', $enterpriseId);
        }
        
        return $grid;
    }

    /**
     * Add enterprise field to forms
     */
    protected function addEnterpriseField(Form $form, $enterpriseId = null)
    {
        $enterpriseId = $enterpriseId ?: Admin::user()->enterprise_id;
        
        $form->hidden('enterprise_id')
             ->rules('required')
             ->default($enterpriseId)
             ->value($enterpriseId);
             
        return $form;
    }

    /**
     * Get user ajax URL for select fields
     */
    protected function getUserAjaxUrl($userType = null)
    {
        $url = url('/api/ajax?' . 
                  'search_by_1=name' .
                  '&search_by_2=id' .
                  '&model=User');
                  
        if ($userType) {
            $url .= '&query_user_type=' . $userType;
        }
        
        return $url;
    }

    /**
     * Configure standard grid settings
     */
    protected function configureStandardGrid(Grid $grid)
    {
        $grid->disableBatchActions();
        $grid->model()->orderBy('id', 'desc');
        
        return $grid;
    }

    /**
     * Add standard date columns to grid
     */
    protected function addStandardDateColumns(Grid $grid)
    {
        $grid->column('created_at', __('Created At'))
            ->display(function ($date) {
                return \App\Models\Utils::my_date_time($date);
            })
            ->sortable();

        $grid->column('updated_at', __('Updated At'))
            ->display(function ($date) {
                return \App\Models\Utils::my_date_time($date);
            })
            ->sortable()
            ->hide();
            
        return $grid;
    }

    /**
     * Add user display column to grid
     */
    protected function addUserColumn(Grid $grid, $field, $label)
    {
        $grid->column($field, __($label))
            ->display(function ($userId) {
                if (!$userId) {
                    return 'N/A';
                }
                
                $user = User::withoutGlobalScope('enterprise')->find($userId);
                return $user ? $user->name : 'N/A';
            })
            ->sortable();
            
        return $grid;
    }

    /**
     * Add status column with badges
     */
    protected function addStatusColumn(Grid $grid, $field = 'status', $statusConfig = [])
    {
        $defaultLabels = [
            'pending' => 'warning',
            'active' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            'approved' => 'success',
            'rejected' => 'danger',
        ];
        
        $labels = array_merge($defaultLabels, $statusConfig);
        
        $grid->column($field, __(ucfirst($field)))
            ->label($labels)
            ->filter(array_keys($labels))
            ->sortable();
            
        return $grid;
    }

    /**
     * Handle common form validation errors
     */
    protected function handleFormErrors(\Exception $e, $action = 'save')
    {
        admin_error("Failed to {$action} record: " . $e->getMessage());
        
        // Log the error for debugging
        Log::error("Admin Controller Error - {$action}: " . $e->getMessage(), [
            'user' => Admin::user()->id ?? 'unknown',
            'enterprise' => Admin::user()->enterprise_id ?? 'unknown',
            'trace' => $e->getTraceAsString()
        ]);
        
        return back()->withInput();
    }

    /**
     * Standardized success message
     */
    protected function showSuccessMessage($action = 'saved')
    {
        admin_success("Record {$action} successfully!");
    }
}

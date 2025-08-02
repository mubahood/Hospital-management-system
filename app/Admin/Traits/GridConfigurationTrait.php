<?php

namespace App\Admin\Traits;

use Encore\Admin\Grid;
use App\Models\Utils;

trait GridConfigurationTrait
{
    /**
     * Apply standard grid configurations
     */
    protected function configureStandardGrid(Grid $grid, $options = [])
    {
        // Default options
        $defaults = [
            'batch_actions' => false,
            'export' => true,
            'filter' => true,
            'create_button' => true,
            'quick_search' => null,
            'per_page' => 20,
            'order_by' => 'id',
            'order_direction' => 'desc'
        ];
        
        $options = array_merge($defaults, $options);
        
        // Configure grid settings
        if (!$options['batch_actions']) {
            $grid->disableBatchActions();
        }
        
        if (!$options['export']) {
            $grid->disableExport();
        }
        
        if (!$options['filter']) {
            $grid->disableFilter();
        }
        
        if (!$options['create_button']) {
            $grid->disableCreateButton();
        }
        
        // Set ordering
        $grid->model()->orderBy($options['order_by'], $options['order_direction']);
        
        // Set pagination
        $grid->paginate($options['per_page']);
        
        // Add quick search if specified
        if ($options['quick_search']) {
            $searchFields = is_array($options['quick_search']) 
                ? $options['quick_search'] 
                : [$options['quick_search']];
            
            $grid->quickSearch(...$searchFields)->placeholder('Quick search...');
        }
        
        return $grid;
    }

    /**
     * Add ID column (usually hidden)
     */
    protected function addIdColumn(Grid $grid, $hidden = true)
    {
        $column = $grid->column('id', 'ID')->sortable();
        
        if ($hidden) {
            $column->hide();
        }
        
        return $grid;
    }

    /**
     * Add formatted date column
     */
    protected function addDateColumn(Grid $grid, $field, $label, $hidden = false)
    {
        $column = $grid->column($field, $label)
            ->display(function ($date) {
                return $date ? Utils::my_date_time($date) : 'N/A';
            })
            ->sortable();
            
        if ($hidden) {
            $column->hide();
        }
        
        return $grid;
    }

    /**
     * Add standard created_at and updated_at columns
     */
    protected function addTimestampColumns(Grid $grid, $hideUpdated = true)
    {
        $this->addDateColumn($grid, 'created_at', 'Created At');
        $this->addDateColumn($grid, 'updated_at', 'Updated At', $hideUpdated);
        
        return $grid;
    }

    /**
     * Add money/currency column with formatting
     */
    protected function addMoneyColumn(Grid $grid, $field, $label, $currency = 'UGX')
    {
        $grid->column($field, $label)
            ->display(function ($amount) use ($currency) {
                return $amount ? number_format($amount, 2) . ' ' . $currency : '0.00 ' . $currency;
            })
            ->sortable();
            
        return $grid;
    }

    /**
     * Add status column with color coding
     */
    protected function addStatusColumn(Grid $grid, $field = 'status', $statusConfig = [], $filterable = true)
    {
        $defaultConfig = [
            'pending' => 'warning',
            'active' => 'info',
            'in_progress' => 'info',
            'completed' => 'success',
            'approved' => 'success',
            'cancelled' => 'danger',
            'rejected' => 'danger',
            'failed' => 'danger'
        ];
        
        $config = array_merge($defaultConfig, $statusConfig);
        
        $column = $grid->column($field, ucfirst($field))
            ->label($config)
            ->sortable();
            
        if ($filterable) {
            $column->filter(array_combine(array_keys($config), array_keys($config)));
        }
        
        return $grid;
    }

    /**
     * Add user reference column
     */
    protected function addUserColumn(Grid $grid, $field, $label, $relationMethod = null)
    {
        if ($relationMethod) {
            // Use relationship
            $grid->column($relationMethod . '.name', $label)
                ->sortable();
        } else {
            // Use direct user lookup
            $grid->column($field, $label)
                ->display(function ($userId) {
                    if (!$userId) return 'N/A';
                    
                    $user = \App\Models\User::withoutGlobalScope('enterprise')->find($userId);
                    return $user ? $user->name : 'N/A';
                })
                ->sortable();
        }
        
        return $grid;
    }

    /**
     * Add action column with custom actions
     */
    protected function addActionColumn(Grid $grid, $customActions = [])
    {
        $grid->actions(function (Grid\Displayers\Actions $actions) use ($customActions) {
            // Default actions (view, edit, delete) are automatically added
            
            // Add custom actions
            foreach ($customActions as $action) {
                if (isset($action['url'], $action['label'])) {
                    $url = is_callable($action['url']) 
                        ? call_user_func($action['url'], $this->row) 
                        : str_replace('{id}', $this->row->id, $action['url']);
                    
                    $class = $action['class'] ?? 'btn btn-sm btn-default';
                    $target = $action['target'] ?? '_self';
                    
                    $actions->append("<a href='{$url}' class='{$class}' target='{$target}'>{$action['label']}</a>");
                }
            }
        });
        
        return $grid;
    }

    /**
     * Add preview/view action
     */
    protected function addPreviewAction(Grid $grid, $routeName, $label = 'Preview')
    {
        $this->addActionColumn($grid, [
            [
                'url' => function($row) use ($routeName) {
                    return route($routeName, ['id' => $row->id]);
                },
                'label' => $label,
                'class' => 'btn btn-sm btn-info',
                'target' => '_blank'
            ]
        ]);
        
        return $grid;
    }

    /**
     * Add boolean column with yes/no display
     */
    protected function addBooleanColumn(Grid $grid, $field, $label)
    {
        $grid->column($field, $label)
            ->display(function ($value) {
                return $value ? 'Yes' : 'No';
            })
            ->label([
                1 => 'success',
                0 => 'default'
            ])
            ->filter([
                1 => 'Yes',
                0 => 'No'
            ])
            ->sortable();
            
        return $grid;
    }

    /**
     * Add text column with truncation
     */
    protected function addTextColumn(Grid $grid, $field, $label, $maxLength = 50, $hidden = false)
    {
        $column = $grid->column($field, $label)
            ->display(function ($text) use ($maxLength) {
                return $text ? (strlen($text) > $maxLength ? substr($text, 0, $maxLength) . '...' : $text) : 'N/A';
            })
            ->sortable();
            
        if ($hidden) {
            $column->hide();
        }
        
        return $grid;
    }

    /**
     * Add number column with formatting
     */
    protected function addNumberColumn(Grid $grid, $field, $label, $decimals = 0)
    {
        $grid->column($field, $label)
            ->display(function ($number) use ($decimals) {
                return $number ? number_format($number, $decimals) : '0';
            })
            ->sortable();
            
        return $grid;
    }
}

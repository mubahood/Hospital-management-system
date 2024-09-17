<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {


    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('meetings', MeetingController::class);
    $router->resource('companies', CompanyController::class);
    $router->resource('departments', DepartmentController::class);
    $router->resource('clients', ClientController::class);
    $router->resource('employees', EmployeesController::class);
    $router->resource('admin-roles', AdminRoleController::class);
    $router->resource('projects', ProjectController::class);
    $router->resource('project-sections', ProjectSectionController::class);
    /*     $router->resource('daily-tasks', TaskController::class);
    $router->resource('weekly-tasks', TaskController::class);
    $router->resource('montly-tasks', TaskController::class); */
    $router->resource('tasks-pending', TaskController::class);
    $router->resource('tasks-manage', TaskController::class);
    $router->resource('tasks-completed', TaskController::class);
    $router->resource('tasks', TaskController::class);
    $router->resource('events', EventController::class);
    $router->get('/calendar', 'HomeController@calendar')->name('calendar');
    $router->resource('patients', PatientController::class);
    $router->resource('patient-records', PatientRecordController::class);
    $router->resource('treatment-records', TreatmentRecordController::class);
    $router->resource('treatment-record-items', TreatmentRecordItemController::class);
    $router->resource('reports', ReportModelController::class);
    $router->resource('targets', TargetController::class);
    $router->resource('gens', GenController::class);

    $router->resource('consultations', ConsultationController::class);
    $router->resource('services', ServiceController::class);
    $router->resource('medical-services', MedicalServiceController::class);
    $router->resource('stock-item-categories', StockItemCategoryController::class);
    $router->resource('stock-items', StockItemController::class);
    $router->resource('stock-out-records', StockOutRecordController::class);
    $router->resource('consultation-billing', BillingController::class);
    $router->resource('consultation-payments', ConsultationPaymentController::class);
    $router->resource('payment-records', PaymentRecordController::class);
    $router->resource('progress-monitoring', ProgressMonitoringController::class);
    $router->resource('cards', CardsController::class);
    $router->resource('card-records', CardRecordController::class);
    $router->resource('flutter-wave-logs', FlutterWaveLogController::class); 
    $router->resource('doses', DoseController::class); 
    $router->resource('dose-item-records', DoseItemRecordController::class);

});

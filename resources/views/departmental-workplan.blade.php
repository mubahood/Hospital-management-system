<?php

$company = $user->company;
$logo_link = url('storage/' . $company->logo);
// $link = url('css/bootstrap-print.css');
use App\Models\Utils;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @include('css')
    <title>{{ $title }}</title>
</head>

<body>
    <table class="w-100 ">
        <tbody>
            <tr>
                <td style="width: 12%;" class="">
                    <img class="img-fluid" src="{{ $logo_link }}" alt="{{ $company->name }}">
                </td>
                <td class=" text-center">
                    <h1 class="h3 ">{{ $company->name }}</h1>
                    <p class="mt-1">Address {{ $company->address }}, {{ $company->p_o_box }}</p>
                    <p class="mt-0">Website: Email: {{ $company->website }}, Email: {{ $company->email }}</p>
                    <p class="mt-0">Tel: <b>{{ $company->phone_number }}</b> , <b>{{ $company->phone_number_2 }}</b>
                    </p>
                </td>
                <td style="width: 10%;"><br></td>
            </tr>
        </tbody>
    </table>
    <hr style="border-width: 4px; color: {{ $company->color }}; border-color: {{ $company->color }};"
        class="mt-3 mb-1">
    <hr style="border-width: 3px; color: black; border-color: black;" class="mb-3 mt-0">

    <p class="text-center fw-600 fs-24 mt-4"><u>Employee Performance Report</u></p>

    <p class="text-right mt-3 fw-600">As On {{ Utils::my_date(now()) }}</p>


    <p class="fs-24 fw-400 mt-2 "><span>EMPLOYEE:</span> <span class="fw-600">{{ $user->name }}</span> </p>

    <p class="fs-24 fw-800 mt-4"><u>Tasks Report Summary</u></p>
    @include('title-detail', ['t' => 'TASKS ASSIGNED', 'd' => $tasks_tot])
    @include('title-detail', ['t' => 'TASKS SUBMITTED', 'd' => $tasks_submited])
    @include('title-detail', ['t' => 'TASKS DONE IN TIME', 'd' => $tasks_done])
    @include('title-detail', ['t' => 'TASKS DONE IN LATE', 'd' => $tasks_done_late])
    @include('title-detail', ['t' => 'NOT ATTENDED TO', 'd' => $tasks_missed])

    {{-- 
        '' => $tasks_done_late,
        'tasks_done' => $tasks_done,
        'tasks_not_submited' => $tasks_not_submited,
        --}}

    <p class="fs-24 fw-600 mt-3 mb-2"><u>Tasks</u></p>
    <table class="table">
        <thead>
            <tr>
                <th class="border-bottom border-top border-left border-right p-1">Ref.</th>
                <th class="border-bottom border-top border-left border-right p-1">Task</th>
                <th class="border-bottom border-top border-left border-right p-1">Due To</th>
                <th class="border-bottom border-top border-left border-right p-1">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($user->tasks as $workplan)
                <tr>
                    <td class="border-bottom border-left border-right p-1">#{{ $workplan->id }}</td>
                    <td class="border-bottom border-left border-right p-1">
                        <p><b>{{ $workplan->name }}</b></p>
                        <p><small>{{ $workplan->task_description }}</small></p>
                    </td>
                    <td class="border-bottom border-left border-right p-1">{{ Utils::my_date($workplan->due_to_date) }}
                    <td class="border-bottom border-left border-right p-1">{{ $workplan->manager_submission_status }}
                    </td>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{-- 
project_id
project_section_id
->assigned_to
name
task_description
due_to_date
	

    --}}
</body>

</html>

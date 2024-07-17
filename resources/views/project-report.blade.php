<?php

$company = $item->company;
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

    @include('widgets.header', ['logo_link' => $logo_link, 'company' => $company])
    <p class="text-center fw-900 fs-30 mt-4 pt-2 pb-2" style="background-color: {{ $company->color }}; color: white;}}">
        Project Status Report
    </p>

    <p class="text-right mt-3 fw-600">As On {{ Utils::my_date(now()) }}</p>


    <p class="fs-18 fw-500 mt-2 text-uppercase "><span>Project Information</span></p>
    <hr class="mt-1 bg-dark mb-3">
    <table class="w-100">
        <tbody>
            <tr>
                <td>
                    @include('title-detail', ['t' => 'PROJECT NAME', 'd' => $item->name, 'style' => '2'])
                    @include('title-detail', ['t' => 'CLIENT', 'd' => $item->client->name, 'style' => '2'])
                    @include('title-detail', [
                        't' => 'CONTACT',
                        'd' => $item->client->email,
                        'style' => '2',
                    ])
                </td>
                <td>
                    @include('title-detail', [
                        't' => 'START DATE',
                        'd' => $item->manager->name,
                        'style' => '2',
                    ])
                    @include('title-detail', [
                        't' => 'CLIENT',
                        'd' => Utils::my_date($item->created_at),
                        'style' => '2',
                    ])
                    @include('title-detail', [
                        't' => 'PLANNED END DATE',
                        'd' => Utils::my_date($item->created_at),
                        'style' => '2',
                    ])
                </td>
            </tr>
        </tbody>
    </table>


    <p class="fs-18 fw-500 mt-3 text-uppercase "><span>Project Status Summary</span></p>
    <hr class="mt-1 bg-dark mb-3">
    <table class="w-100">
        <tbody>
            <tr>
                <td style="width: 40%">
                    <p class="fs-18 fw-400 mt-3 "><span>Completed Work</span></p>
                    <p class="fw-900 fs-30 mt-2 mb-2 text-center">{{ $item->progress }}%</p>
                </td>
                <td style="border-left: 1px solid black;">
                    <ol>
                        @foreach ($item->project_sections as $sec)
                            @if ($sec->progress > 99)
                                <li>{{ $sec->name }}</li>
                            @endif
                        @endforeach
                    </ol>
                </td>
            </tr>
        </tbody>
    </table>

    <style>
        .table {
            border-collapse: collapse;
            width: 100%;
        }

        .table td,
        .table th {
            border: 1px solid black;
            padding: 2px;
        }

        .table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table tr:hover {
            background-color: #ddd;
        }

        .table th {
            padding-top: 1px;
            padding-left: 1px;
            padding-bottom: 1px;
            text-align: left;
            background-color: #dcedf4;
            color: black;
        }
    </style>

    <p class="fs-18 fw-400 mb-2 mt-2"><span>Pending Deliverables</span></p>
    <table class="table">
        <thead>
            <tr>
                <th class="border-bottom border-top border-left border-right p-1">Sn.</th>
                <th class="border-bottom border-top border-left border-right p-1">Deliverable</th>
                <th class="border-bottom border-top border-left border-right p-1">Progress</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 0; ?>
            @foreach ($item->project_sections as $sec)
                <?php
                if ($sec->progress > 99) {
                    continue;
                }
                $i++; ?>
                <tr>
                    <td class="border-bottom border-left border-right p-1" style="width: 40px">{{ $i }}.
                    </td>
                    <td class="border-bottom border-left border-right p-1 w-100">
                        {{ $sec->name }}
                    </td>
                    <td class="border-bottom border-left border-right p-1 text-center" style="width: 40px">
                        <b>{{ $sec->progress }}%</b>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="fs-18 fw-500 mt-4 text-uppercase "><span>Project's Health</span></p>
    <hr class="mt-1 bg-dark mb-2">
    <table class="w-100">
        <tbody>
            <tr>
                <td>

                    @include('title-detail', [
                        't' => 'Budget Overview',
                        'd' => $item->budget_overview,
                        'style' => '2',
                    ])
                    @include('title-detail', [
                        't' => 'Schedule Overview',
                        'd' => $item->schedule_overview,
                        'style' => '2',
                    ])
                </td>
                <td>

                    @include('title-detail', [
                        't' => 'Project Risks & Issues',
                        'd' => $item->risks_issues,
                        'style' => '2',
                    ])
                    @include('title-detail', [
                        't' => 'Concerns/Recommendations',
                        'd' => $item->concerns_recommendations,
                        'style' => '2',
                    ])
                </td>
            </tr>
        </tbody>
    </table>
    <hr class="bg-dark">
    <p class="text-center fw-600 mb-0">This report is generated by {{ $item->manager->name }} on
        {{ Utils::my_date(now()) }}</p> <br>
    <p class="text-center fw-600"><small>Powered by <a href="https://8technologies.net">{{ env('APP_NAME') }} - Eight
                Tech
                Consults Ltd</a></small></p>

</body>

</html>

<?php

$company = $item->company;
$logo_link = url('storage/' . $company->logo);
if (!isset($title)) {
    $title = 'Project Status Report';
}
use App\Models\Utils;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @include('css', $company)
    @include('widgets.print-css', $company)
    <title>{{ $item->title }}</title>
</head>

<body>
    @include('widgets.header', ['logo_link' => $logo_link, 'company' => $item->company])
    <p class="text-center fw-900 fs-14 mt-2 pt-1 pb-1 text-uppercase fw-700"
        style="background-color: {{ $item->company->color }}; color: white;}}">
        {{ $title }}
    </p>
    <p class="text-right mt-2 fw-800">As On {{ Utils::my_date(now()) }}</p>

    <table class="w-100">
        <tbody>
            <tr>
                <td class="" style="width: 33.33%">
                    <u class="fw-600 text-uppercase">Milestones Achieved</u>
                    <ul class="pl-3">
                        <li>Project Charter</li>
                        <li>Project Plan</li>
                        <li>Project Budget</li>
                        <li>Project Team</li>
                        <li>Project Kick-off</li>
                        <li>Project Status Report</li>
                    </ul>
                </td>
                <td class="" style="width: 33.33%">
                    <u class="fw-600 text-uppercase text-uppercase">Project weights</u>
                    <ul class="pl-3">
                        <img class="img-fluid"
                            src="https://quickchart.io/chart?c={type:%27pie%27,data:{labels:[2012,2013,2014,2015,2016],datasets:[{label:%27Users%27,data:[120,120,50,180,120]}]}}"
                            alt="chat 1">
                    </ul>
                </td>
                <td class="" style="width: 33.33%">
                    <u class="fw-600 text-uppercase">Work load</u>
                    <img class="img-fluid"
                        src="https://quickchart.io/chart?c={type:%27bar%27,data:{labels:[2012,2013,2014,2015,2016],datasets:[{label:%27Users%27,data:[120,120,50,180,120]}]}}"
                        alt="chat 2">
                </td>
            </tr>
        </tbody>
    </table>


    <table class="w-100 mt-1 ">
        <tr>
            <td style="width: 50% ; padding-left: 0rem; vertical-align: top; align-content: flex-start;">
                <u class="fw-600 text-uppercase mt-2 ">Projects progress</u>
                <div class="mt-2">
                    @php $progress = 40; @endphp
                    <p class=" fw-700">ICT 4 Farmers {{ $progress }}%</p>
                    <div class="progress border border-dark "
                        style=" height: 20px!important; border-radius: 0%; border-width: 3px; background-color: rgb(229, 226, 226); ">
                        <div class="progress-bar" role="progressbar"
                            style=" width: {{ $progress }}%; height: 20px!important; color: black; padding-top: 0px; font-size: 14px; font-weight: 800; line-height: 20px; background-color: {{ $item->company->color }}; "
                            aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $progress }}%</div>
                    </div>
                    <span>Deadline:</span>
                    <span style="float: right;">12<sup>th</sup> Jan, 2024</span>
                </div>
                <hr style="margin: 0;" class="mt-2 mb-2">
                <div>
                    @php $progress = 25; @endphp
                    <p class=" fw-700">ICT 4 Farmers {{ $progress }}%</p>
                    <div class="progress border border-dark "
                        style=" height: 20px!important; border-radius: 0%; border-width: 3px; background-color: rgb(229, 226, 226); ">
                        <div class="progress-bar" role="progressbar"
                            style=" width: {{ $progress }}%; height: 20px!important; color: black; padding-top: 0px; font-size: 14px; font-weight: 800; line-height: 20px; background-color: {{ $item->company->color }}; "
                            aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $progress }}%</div>
                    </div>
                    <span>Deadline:</span>
                    <span style="float: right;">12<sup>th</sup> Jan, 2024</span>
                </div>
                <hr style="margin: 0;" class="mt-2 mb-2">
                <div>
                    @php $progress = 85; @endphp
                    <p class=" fw-700">ICT 4 Farmers {{ $progress }}%</p>
                    <div class="progress border border-dark "
                        style=" height: 20px!important; border-radius: 0%; border-width: 3px; background-color: rgb(229, 226, 226); ">
                        <div class="progress-bar" role="progressbar"
                            style=" width: {{ $progress }}%; height: 20px!important; color: black; padding-top: 0px; font-size: 14px; font-weight: 800; line-height: 20px; background-color: {{ $item->company->color }}; "
                            aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $progress }}%</div>
                    </div>
                    <span>Deadline:</span>
                    <span style="float: right;">12<sup>th</sup> Jan, 2024</span>
                </div>
                <hr style="margin: 0;" class="mt-2 mb-2">
                <div>
                    @php $progress = 30; @endphp
                    <p class=" fw-700">ICT 4 Farmers {{ $progress }}%</p>
                    <div class="progress border border-dark "
                        style=" height: 20px!important; border-radius: 0%; border-width: 3px; background-color: rgb(229, 226, 226); ">
                        <div class="progress-bar" role="progressbar"
                            style=" width: {{ $progress }}%; height: 20px!important; color: black; padding-top: 0px; font-size: 14px; font-weight: 800; line-height: 20px; background-color: {{ $item->company->color }}; "
                            aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $progress }}%</div>
                    </div>
                    <span>Deadline:</span>
                    <span style="float: right;">12<sup>th</sup> Jan, 2024</span>
                </div>
            </td>
            <td style="width: 50% ; padding-left: 2rem; vertical-align: top; align-content: flex-start;"
                class="p-0 pl-3">
                <u class="fw-600 text-uppercase mb-2">Feedback and reccomendations</u>
                <ul class="pl-3 fs-8 mt-2">
                    <li class="fs-8" style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Charter...
                        By John... [Done]</li>
                    <li>Project Plan... By John... [Done]</li>
                    <li style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Budget... By John...
                        [Done]</li>
                    <li>Project Team... By John... [Pending]</li>
                    <li style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Kick-off [Pending]</li>
                    <li>Project Status Report... By John... [Done]</li>
                    <li style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Budget... By John...
                        [Done]</li>
                    <li>Project Team... By John... [Pending]</li>
                    <li style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Kick-off [Pending]</li>
                    <li>Project Status Report... By John... [Done]</li>
                </ul>
            </td>
        </tr>
    </table>

    <table class="w-100 mt-3 ">
        <tr>
            <td style="width: 25% ; padding-left: 0rem; vertical-align: top; align-content: flex-start;" class="">

                <u class="fw-600 text-uppercase   ">Task Completion</u>
                <br>
                <br>
                <div
                    style="
                 width: 200px; height: 125px; border: 2px black solid; font-weight: 800;
                 font-size: 45px; border-radius: 100%; text-align: center; margin: 0 auto; background-color: rgb(246, 246, 246);
                 vertical-align: top; align-content: flex-center; 
                 padding-top: 70px;
                 align-content: center;
                 align-items: center;
                 align-self: center;
                 
                 ">


                    75%

                </div>
            </td>
            <td style="width: 50% ; padding-left: 2rem; vertical-align: top; align-content: flex-start;"
                class="p-0 pl-3 ">
                <u class="fw-600 text-uppercase mb-2">Feedback and reccomendations</u>
                <ul class="pl-3 fs-8 mt-2">
                    <li class="fs-8" style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Charter...
                        By John... [Done]</li>
                    <li>Project Plan... By John... [Done]</li>
                    <li style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Budget... By John...
                        [Done]</li>
                    <li>Project Team... By John... [Pending]</li>
                    <li style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Kick-off [Pending]</li>
                    <li>Project Status Report... By John... [Done]</li>
                    <li style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Budget... By John...
                        [Done]</li>
                    <li>Project Team... By John... [Pending]</li>
                    <li style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Kick-off [Pending]</li>
                    <li>Project Status Report... By John... [Done]</li>
                </ul>
            </td>
        </tr>
    </table>


    <div style="page-break-before: always;">
        <p class="text-center fw-900 fs-14 mt-2 pt-1 pb-1 text-uppercase fw-700"
            style="background-color: {{ $item->company->color }}; color: white;}}">
            Next workplan
        </p>
        <p class="text-right mt-2 fw-800">As On {{ Utils::my_date(now()) }}</p>

        <table class="w-100">
            <tbody>
                <tr>
                    <td class="" style="width: 33.33%">
                        <u class="fw-600 text-uppercase">Targets</u>
                        <ul class="pl-3">
                            <li>Project Charter</li>
                            <li>Project Plan</li>
                            <li>Project Budget</li>
                            <li>Project Team</li>
                            <li>Project Kick-off</li>
                            <li>Project Status Report</li>
                        </ul>
                    </td>
                    <td class="" style="width: 33.33%">
                        <u class="fw-600 text-uppercase text-uppercase">Project weights</u>
                        <ul class="pl-3">
                            <img class="img-fluid"
                            src="https://quickchart.io/chart?c={type:%27doughnut%27,data:{labels:[2012,2013,2014,2015,2016],datasets:[{label:%27Users%27,data:[120,120,50,180,120]}]}}"
                            alt="chat 1">
                        </ul>
                    </td>
                    <td class="" style="width: 33.33%">
                        <u class="fw-600 text-uppercase">Work load</u>
                        <img class="img-fluid"
                        src="https://quickchart.io/chart?c={type:%27bar%27,data:{labels:[2012,2013,2014,2015,2016],datasets:[{label:%27Users%27,data:[120,120,50,180,120]}]}}"
                        alt="chat 2">
                    </td>
                </tr>
            </tbody>
        </table>

        <u class="fw-600 text-uppercase mb-2">Feedback and reccomendations</u>
        <ul class="pl-3 fs-8 mt-2">
            <li class="fs-8" style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Charter...
                By John... [Done]</li>
            <li>Project Plan... By John... [Done]</li>
            <li style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Budget... By John...
                [Done]</li>
            <li>Project Team... By John... [Pending]</li>
            <li style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Kick-off [Pending]</li>
            <li>Project Status Report... By John... [Done]</li>
            <li style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Budget... By John...
                [Done]</li>
            <li>Project Team... By John... [Pending]</li>
            <li style="background-color: rgb(134, 248, 248);font-size: 8px!;">Project Kick-off [Pending]</li>
            <li>Project Status Report... By John... [Done]</li>
        </ul>
    </div>

</body>

</html>

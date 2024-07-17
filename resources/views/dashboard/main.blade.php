<?php
//import Utils class from models folder
use App\Models\Utils;
?>
<style>
    .quadrat {
        -webkit-animation: NAME-YOUR-ANIMATION 1s infinite;
        /* Safari 4+ */
        -moz-animation: NAME-YOUR-ANIMATION 1s infinite;
        /* Fx 5+ */
        -o-animation: NAME-YOUR-ANIMATION 1s infinite;
        /* Opera 12+ */
        animation: NAME-YOUR-ANIMATION 1s infinite;
        /* IE 10+, Fx 29+ */
    }

    @-webkit-keyframes NAME-YOUR-ANIMATION {

        0%,
        49% {
            background-color: #0761BB;
            border: 3px solid #ffffff;
            color: white;
        }

        50%,
        100% {
            background-color: #ffffff;
            border: 3px solid #0761BB;
            color: #000;
        }
    }
</style>
<div class="container-fluid p-0 m-0">
    <div class="d-flex">
        <div class="mr-auto p-2">
            <p class="fs-16 fw-400">{!! $greet !!}</p>
        </div>
        <div class="p-2">
            <p class="fs-10">Today is</p>
            <p class="fs-10 fw-900 lh-1">
                {{ date('l') . ', ' . date('d') . ' ' . date('M') . ' ' . date('Y') }}
            </p>
        </div>
    </div>
    <hr class="p-0 m-0">

    <div class="row">
        <a href="{{ admin_url('tasks-pending') }}" class="col-sm-6 col-lg-3">
            <div
                class="card mt-2 mt-md-4 mb-3 border border-primary border-5 
            {{ $man->tasks_pending_items->count() > 0 ? 'quadrat' : '' }}
            ">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="mr-auto">
                            <p class="fw-900 fs-12 mb-3">My Pending Tasks</p>
                            <p class="fs-46 fw-100 lh-1">{{ $man->tasks_pending_items->count() }}</p>
                        </div>
                        <div class="fs-46 lh-1 mt-4">
                            📝
                        </div>
                    </div>
                </div>
            </div>
        </a>

        <a href="{{ admin_url('tasks-manage') }}" class="col-sm-6 col-lg-3">
            <div
                class="card mt-2 mt-md-4 mb-3 border border-primary border-5
            {{ $man->manage_tasks->count() > 0 ? 'quadrat' : '' }}
            ">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="mr-auto">
                            <p class="fw-900 fs-12 mb-3">Pending Supervision</p>
                            <p class="fs-46 fw-100 lh-1">{{ $man->manage_tasks->count() }}</p>
                        </div>
                        <div class="fs-46 lh-1 mt-4">
                            📑
                        </div>
                    </div>
                </div>
            </div>
        </a>

        <div class="col-sm-6 col-lg-3">
            <div class="card mt-2 mt-md-4 mb-3 border border-primary border-5">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="mr-auto">
                            <p class="fw-900 fs-12 mb-3">My Workload</p>
                            <p class="fs-46 fw-100 lh-1">{{ $man->tasks_pending_items->sum('hours') }} Hours</p>
                        </div>
                        <div class="fs-46 lh-1 mt-4">
                            🏋️
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card mt-2 mt-md-4 mb-3 border border-primary border-5">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="mr-auto">
                            <p class="fw-900 fs-12 mb-3">Work Accomplished</p>
                            <p class="fs-46 fw-100 lh-1">{{ $man->tasks_completed->sum('hours') }} Hours</p>
                        </div>
                        <div class="fs-46 lh-1 mt-4">
                            🏅
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-lg-3">
            <div class="card mt-3">
                <div class="card-header py-3 py-md-4">
                    <p class="fs-16 fw-700 lh-1 text-dark">Targets & Goals</p>
                </div>
                <div class="card-body mb-0">
                    @if ($man->targets->count() < 1)
                        <div class="d-flex justify-content-center align-items-center" style="height: 100%">
                            <p class="fs-14 fw-700 text-center">No targets set yet</p>
                        </div>
                    @else
                        @foreach ($man->targets as $item)
                            <div class="d-flex ">
                                <div class="fs-28 p-0 m-0 lh-4">
                                    🎯
                                </div>
                                <div class="ml-2  " style="width: 100%">
                                    <p class="fs-12 fw-700 mb-0 pb-1 lh-5">{{ $item->title }}</p>
                                    <p class="p-0 m-0 mt-1 fs-10 d-flex justify-content-between">
                                        <span>{{ $item->lead->short_name }}</span>
                                        <span
                                            class="text-primary fw-800">{{ Utils::my_date_2($item->due_date) }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="p-0 m-0 my-3" style="border: .5px rgb(177, 172, 172) dashed"></div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card mt-3">
                <div class="card-header py-3 py-md-4">
                    <p class="fs-16 fw-700 lh-1 text-dark">Milestones</p>
                </div>
                <div class="card-body mb-0">
                    {{-- data on $man->milestones --}}

                    @if ($man->milestones->count() < 1)
                        <div class="d-flex justify-content-center align-items-center" style="height: 100%">
                            <p class="fs-14 fw-700 text-center">No milestones reached yet.</p>
                        </div>
                    @else
                        @foreach ($man->milestones as $item)
                            <div class="d-flex ">
                                <div class="fs-28 p-0 m-0 lh-4">
                                    🏆
                                </div>
                                <div class="ml-2  " style="width: 100%">
                                    <p class="fs-12 fw-700 mb-0 pb-1 lh-5">{{ $item->title }}</p>
                                    <p class="p-0 m-0 mt-1 fs-10 d-flex justify-content-between">
                                        <span>{{ Str::limit($item->department->name, 20, '...') }}</span>
                                        <span
                                            class="text-primary fw-800">{{ Utils::my_date_2($item->due_date) }}</span>
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>


        <div class="col-sm-6 col-lg-3">
            <div class="card mt-3">
                <div class="card-header  py-3 py-md-4">
                    <p class="fs-16 fw-700 lh-1 text-dark">Project weights</p>
                </div>
                <div class="card-body">
                    <canvas id="weights_chart" width="500" height="540"></canvas>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card mt-3">
                <div class="card-header  py-3 py-md-4">
                    <p class="fs-16 fw-700 lh-1 text-dark">Total Projects Completion</p>
                </div>
                <div class="card-body">
                    <canvas id="myChart2" width="500" height="540"></canvas>
                </div>
            </div>
        </div>
    </div>
    @php
        $i = 0;
    @endphp
    <div class="row">
        <div class="col-sm-6 col-lg-4">
            <div class="card mt-3">
                <div class="card-header py-3 py-md-4">
                    <p class="fs-16 fw-700 lh-1 text-dark">Projects Health</p>
                </div>
                <div class="card-body mb-0">
                    @foreach ($man->project_weights as $item)
                        <div>
                            <p>
                                <span class="fs-14  fw-700">{{ $item['name'] }}
                                    ({{ $item['progress'] }}%)
                                </span>
                            </p>
                            <div class="progress mb-0 mt-1 mb-1 "
                                style="height: 15px; background-color: rgb(190, 190, 190); border: 1px solid grey; ">
                                <div class="progress-bar fw-800 text-white" role="progressbar"
                                    style="width: {{ $item['progress'] }}%; background-color: {{ $item['color'] }};"
                                    aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                    {{ $item['progress'] }}%</div>
                            </div>
                            <div class="d-flex">
                                <p class="fs-14 fw-400 mr-auto">{{ $item['project']->manager->name }}</p>
                                <p class="fs-14 fw-700 float-right">Deadline:
                                    {{ Utils::my_date_2($item['project']->end_date) }}</p>
                            </div>
                        </div>
                        @php
                            $i++;
                            if ($i > 4) {
                                //break;
                            }
                        @endphp
                        <div class="p-0 m-0 my-2" style="border: .5px rgb(177, 172, 172) dashed"></div>
                    @endforeach
                </div>
            </div>
        </div>


        <div class="col-sm-6 col-lg-5">
            <div class="card mt-3">
                <div class="card-header py-3 py-md-4">
                    <p class="fs-16 fw-700 lh-1 text-dark">Employees workload</p>
                </div>
                <div class="card-body mb-0">
                    <canvas height="200" id="myChart3"></canvas>
                </div>
            </div>
        </div>


        <div class="col-sm-6 col-lg-3">
            <div class="card mt-3">
                <div class="card-header py-3 py-md-4">
                    <p class="fs-16 fw-700 lh-1 text-dark">Employees' Reports</p>
                </div>
                <div class="card-body mb-0">
                    @php
                        $i = 0;
                    @endphp
                    @foreach ($man->employees as $item)
                        @php
                            $i++;
                            if ($i > 6) {
                                //break;
                            }
                        @endphp
                        <div class="d-flex mb-3">
                            <div class="fs-28 p-0 m-0 lh-4">
                                <img width="42" height="42"
                                    style="border-radius: 50%; border: 1px solid rgb(177, 172, 172);"
                                    src="{{ $item->avatar ? asset('storage/' . $item->avatar) : asset('img/avatar.png') }}"
                                    alt="">
                            </div>
                            <div class="ml-2  " style="width: 100%">
                                <div class="">
                                    <p class="fs-12 fw-700 mb-0 pb-1 lh-5">{{ $item->name }}</p>
                                    <p class="fs-10 fw-500 mb-0 pb-1 lh-5">XYX Department</p>
                                </div>
                                <p class="p-0 m-0 mt-0 fs-10 d-flex justify-content-between">
                                    <span>Last seen: 2 Days ago</span>
                                    <span class="text-primary fw-800">Print report</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>



@php
    $project_weights_names = [];
    $project_weights_values = [];
    foreach ($man->project_weights as $key => $value) {
        $project_weights_names[] = $value['name'];
        $project_weights_values[] = $value['tasks'];
    }
    //add more colors if like these
    $colors = [
        '#1A84FF',
        '#4B0082', // Indigo
        '#8B4513', // Saddle Brown
        '#FF1493', // Deep Pink
        '#00FFFF', // Cyan / Aqua
        '#FFD700', // Gold
        '#00FF7F', // Spring Green
        '#00BFFF', // Deep Sky Blue
        '#800000', // Maroon
        '#8A2BE2', // Blue Violet
        '#FF5733', // Orange
        '#7239E9',
        '#F6C001',
        '#16C653',
        '#F8275A',
        '#43CED7',
        '#C0C0C0',
    ];

@endphp
<script>
    $(function() {
        var ctx = document.getElementById("weights_chart").getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            options: {
                responsive: true,
                legend: {
                    position: 'none',
                },
                plugins: {
                    labels: {
                        // render 'label', 'value', 'percentage', 'image' or custom function, default is 'percentage'
                        render: function(args) {
                            //sub string of lable in 3 
                            if (args.percentage < 10) {
                                return args.percentage + "%";
                            } else {
                                return args.label.substring(0, 6) + "\n" + args.percentage + "%";
                            }
                        },
                        // font color, can be color array for each data or function for dynamic color, default is defaultFontColor
                        fontColor: '#fff',
                        // font style, default is defaultFontStyle
                        fontStyle: 'bold',
                    }
                },
            },
            data: {
                labels: <?php echo json_encode($project_weights_names); ?>,
                datasets: [{
                    label: '# of Votes',
                    data: <?php echo json_encode($project_weights_values); ?>,
                    backgroundColor: <?php echo json_encode($colors); ?>,
                    borderColor: <?php echo json_encode($colors); ?>,
                }]
            },
        });

        var ctx = document.getElementById("myChart3").getContext('2d');
        var myChart3 = new Chart(ctx, {
            type: 'bar',
            options: {
                responsive: true,
                legend: {
                    position: 'top',
                },
                plugins: {
                    labels: {
                        // render 'label', 'value', 'percentage', 'image' or custom function, default is 'percentage'
                        render: function(args) {
                            return args.value + "hrs";
                            //sub string of lable in 3 
                            if (args.percentage < 10) {
                                return args.percentage + "hrs";
                            } else {
                                return args.label.substring(0, 3) + "\n" + args.percentage + "%";
                            }
                            return args.label.substring(0, 3) + "\n" + args.percentage + "%";
                        },

                        // precision for percentage, default is 0
                        precision: 0,

                        // identifies whether or not labels of value 0 are displayed, default is false
                        showZero: true,

                        // font size, default is defaultFontSize
                        fontSize: 10,

                        // font color, can be color array for each data or function for dynamic color, default is defaultFontColor
                        fontColor: '#000',

                        // font style, default is defaultFontStyle
                        fontStyle: 'bold',

                        // font family, default is defaultFontFamily
                        fontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",

                        // draw text shadows under labels, default is false
                        textShadow: true,

                        // text shadow intensity, default is 6
                        shadowBlur: 10,

                        // text shadow X offset, default is 3
                        shadowOffsetX: -5,

                        // text shadow Y offset, default is 3
                        shadowOffsetY: 5,

                        // text shadow color, default is 'rgba(0,0,0,0.3)'
                        shadowColor: 'rgba(0,0,0,0.3)',

                        // draw label in arc, default is false
                        // bar chart ignores this
                        arc: true,

                        // position to draw label, available value is 'default', 'border' and 'outside'
                        // bar chart ignores this
                        // default is 'default'
                        position: 'default',

                        // draw label even it's overlap, default is true
                        // bar chart ignores this
                        overlap: true,

                        // show the real calculated percentages from the values and don't apply the additional logic to fit the percentages to 100 in total, default is false
                        showActualPercentages: true,

                        // set images when `render` is 'image'
                        images: [{
                            src: 'image.png',
                            width: 16,
                            height: 16
                        }],

                        // add padding when position is `outside`
                        // default is 2
                        outsidePadding: 4,

                        // add margin of text when position is `outside` or `border`
                        // default is 2
                        textMargin: 4
                    }
                },
            },
            data: {
                labels: <?php echo json_encode($man->employees->pluck('last_name')); ?>,
                datasets: [{
                        label: 'Hours Accomplished',
                        type: 'line',
                        data: <?php echo json_encode($man->employees->pluck('work_load_completed')); ?>,
                        borderColor: 'red',
                    },
                    {
                        label: 'Workload',
                        data: <?php echo json_encode($man->employees->pluck('work_load_pending')); ?>,
                        backgroundColor: <?php echo json_encode($colors); ?>,
                        borderColor: <?php echo json_encode($colors); ?>,
                    },
                ]
            },
        });

        var ctx = document.getElementById("myChart2").getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'pie',
            options: {
                responsive: true,
                legend: {
                    position: 'none',
                },
                plugins: {
                    labels: {
                        // render 'label', 'value', 'percentage', 'image' or custom function, default is 'percentage'
                        render: function(args) {
                            //sub string of lable in 3 
                            if (args.percentage < 10) {
                                return args.percentage + "%";
                            }
                            return args.label + "\n" + args.percentage + "%";
                        },

                        // precision for percentage, default is 0
                        precision: 0,

                        // identifies whether or not labels of value 0 are displayed, default is false
                        showZero: true,

                        // font size, default is defaultFontSize
                        fontSize: 20,

                        // font color, can be color array for each data or function for dynamic color, default is defaultFontColor
                        fontColor: '#fff',

                        // font style, default is defaultFontStyle
                        fontStyle: 'bold',

                        // font family, default is defaultFontFamily
                        fontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",

                        // draw text shadows under labels, default is false
                        textShadow: true,

                        // text shadow intensity, default is 6
                        shadowBlur: 10,

                        // text shadow X offset, default is 3
                        shadowOffsetX: -5,

                        // text shadow Y offset, default is 3
                        shadowOffsetY: 5,

                        // text shadow color, default is 'rgba(0,0,0,0.3)'
                        shadowColor: 'rgba(255,0,0,0.75)',

                        // draw label in arc, default is false
                        // bar chart ignores this
                        arc: false,

                        // position to draw label, available value is 'default', 'border' and 'outside'
                        // bar chart ignores this
                        // default is 'default'
                        position: 'default',

                        // draw label even it's overlap, default is true
                        // bar chart ignores this
                        overlap: true,

                        // show the real calculated percentages from the values and don't apply the additional logic to fit the percentages to 100 in total, default is false
                        showActualPercentages: true,


                        // add padding when position is `outside`
                        // default is 2
                        outsidePadding: 4,

                        // add margin of text when position is `outside` or `border`
                        // default is 2
                        textMargin: 4
                    }
                },
            },
            data: {
                labels: ["Pending", "Completed"],
                datasets: [{
                    label: '# of Votes',
                    data: [<?php echo $man->total_projects_progress_remaining; ?>, <?php echo $man->total_projects_progress; ?>],
                    backgroundColor: [
                        'gray',
                        'green',
                    ],
                    borderColor: [
                        'gray',
                        'green',
                    ],
                }]
            },
        });
    });
</script>

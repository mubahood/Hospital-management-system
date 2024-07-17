<?php
use App\Models\Utils;
?> 
<div class="card  mb-4 mb-md-5 border-0">
    <!--begin::Header-->
    <div class="d-flex justify-content-between px-3 pt-2 px-md-4 border-bottom">
        <h4 style="line-height: 1; margrin: 0; " class="fs-22 fw-800">
            Persons with Disabilities by Districts
        </h4>
    </div>
    <div class="card-body py-2 py-md-3">
        <canvas id="by-districts" style="width: 100%;"></canvas>
    </div>
</div>


<script>
    $(function() {
        var config = {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [370, 57, 101, 210, 259, 712, 100,11,53,12,187],
                    backgroundColor: [
                        '#8EFCDF',
                        '#D0B1FD',
                        '#F43DE3',
                        '#7D57F8',
                        '#34F1B7',
                        '#431B02',
                        '#C71C5D',
                        '#23A2E9',
                        '#868686',
                        '#F6DE5C',
                    ],
                    label: 'Dataset 1'
                }],
                labels: [
                    'Kampala',
                    'Kasese',
                    'Mbarara',
                    'Arua',
                    'Gulu',
                    'Jinja',
                    'Mbale',
                    'Kumi',
                    'Soroti',
                    'Wakiso',
                    'Others',
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'left',
                    display: true, 
                },
                title: {
                    display: false,
                    text: 'Persons with Disabilities by Categories'
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        };

        var ctx = document.getElementById('by-districts').getContext('2d');
        new Chart(ctx, config);
    });
</script>

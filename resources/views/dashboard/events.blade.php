<?php
use App\Models\Utils;
?><style>
    .ext-icon {
        color: rgba(0, 0, 0, 0.5);
        margin-left: 10px;
    }

    .installed {
        color: #00a65a;
        margin-right: 10px;
    }

    .card {
        border-radius: 5px;
    }

    .case-item:hover {
        background-color: rgb(254, 254, 254);
    }
</style>
<div class="card  mb-4 mb-md-5 border-0">
    <!--begin::Header-->
    <div class="d-flex justify-content-between px-3 px-md-4 ">
        <h3>
            <b>Events</b>
        </h3>
        <div>
            <a href="{{ admin_url('/events') }}" class="btn btn-sm btn-primary mt-md-4 mt-4">
                View All
            </a>
        </div>
    </div>
    <div class="card-body py-2 py-md-3">
        @foreach ($items as $i)
            <div class="d-flex align-items-center mb-4 case-item">
                <a href="{{ url('/dinner') }}" target="_blank" title="Read more about this event"
                    style="border-left: solid rgb(44, 132, 3) 5px;"
                    class="flex-grow-1 pl-2 pl-md-3 border-primary text-primary">

                    <b>{{ $i->title }}</b>

                    <span class="text-muted fw-bold d-block">
                        {{ $i->venue_name }}, {{ Utils::my_date($i->created_at) }}
                    </span>
                </a>
                <a href="{{ admin_url('event-bookings/create?event=' . $i->id) }}" class="badge bg-primary ">BOOK A
                    SEAT</a>
            </div>
        @endforeach
    </div>
</div>

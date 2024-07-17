<?php
use App\Models\Utils;
?>
<div class="card mb-4 mb-md-5 border-0 ">
    <div class="card-header p-0 bg-primary rounded-top "
        style="border-top-left-radius: 1rem !important; border-top-right-radius: 1rem !important;">
        <h3 class="px-4 pb-2  text-white py-4 fs-20 fw-700"><b>Completed tasks</b></h3>
    </div>
    <div class="card-body p-0 ">
        <div class="list-group list-group-flush p-0">
            @foreach ([] as $item)
                <hr class="p-0 m-0">
                <a href="{{ admin_url('events/' . $item->id) }}" target="_blank" title="View Event Details"
                    class="list-group-item list-group-item-action flex-column align-items-start py-2">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><b></b></h5>
                        <small><b class="text-primay">{{ Utils::my_date_time_1($item->created_at) }}</b></small>
                    </div>
                    <p class="mb-1">
                        {{ $item->name }}
                    </p>
                    <small class="text-muted">{{ $item->location }}</small>
                </a>
            @endforeach
        </div>
    </div>
</div>

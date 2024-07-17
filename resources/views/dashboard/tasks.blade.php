<?php
use App\Models\Utils;
?>
<div class="card mb-4 mb-md-5 border-0 ">
    <div class="card-header p-0 bg-primary rounded-top "
        style="border-top-left-radius: 1rem !important; border-top-right-radius: 1rem !important;">
        <h3 class="px-4 pb-2  text-white py-4 fs-20 fw-700"><b>{{ $title }}</b></h3>
    </div>
    <div class="card-body p-0 ">
        <div class="list-group list-group-flush p-0">

            @if (count($items) == 0)
                <br>
                <h5 class="mb-1 text-center mt-4"><b>No {{ $title }}. </b></h5>
                <br>
            @else
                @foreach ($items as $item)
                    <hr class="p-0 m-0">
                    <a href="{{ admin_url('tasks/' . $item->id) }}/edit" target="_blank" title="Click to update task"
                        class="list-group-item list-group-item-action flex-column align-items-start py-2">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><b>{{-- {{ $item->name }} --}}</b></h5>
                            <small><b class="text-primay">{{ Utils::my_date_time_1($item->due_to_date) }}</b></small>
                        </div>
                        <p class="mb-1">
                            {{ $item->name }}
                        </p>
                        <small class="text-muted">{{ $item->assigned_to_user->name }}</small>
                    </a>
                @endforeach
            @endif



        </div>
    </div>
</div>

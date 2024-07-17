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
</style>
<div class="card  mb-4 mb-md-5 border-0">
    <!--begin::Header-->
    <div class="d-flex justify-content-between px-3 px-md-4 ">
        <h3>
            <b>Recently joined members</b>
        </h3>
        <div>
            <a href="{{ admin_url('/members') }}" class="btn btn-sm btn-primary mt-md-4 mt-4">
                View All
            </a>
        </div>
    </div>
    <div class="card-body py-0">
        <!--begin::Table container-->
        <div class="table-responsive">
            <!--begin::Table-->
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <!--begin::Table head-->
                <thead>
                    <tr class="fw-bolder text-muted">
                        <th class="min-w-200px">Member</th>
                        <th class="min-w-150px">Group</th>
                        <th class="min-w-150px">Current address</th>
                        <th class="text-right">Connect</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($items as $i)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div
                                        style="
                                    background-image: url({{ $i->avatar }});
                                    background-position: center;
                                    background-size: cover;
                                    border-radius: 8px;
                                    width: 8rem!important; height: 8rem!important;
                                    ">
                                    </div>
                                    <div class="d-flex justify-content-start flex-column pl-3">
                                        <a href="#" style="color: black; font-weight: 600;"
                                            class="text-dark fw-bolder text-hover-primary fs-6">{{ Str::limit($i->name, 20) }}</a>
                                        <span
                                            class="text-muted fw-bold text-muted d-block fs-7">{{ $i->country ?? '- ' }}</span>
                                        <span class="text-muted fw-bold text-muted d-block fs-7">
                                            <b class="p-0 m-0 small text-dark">SEX:</b>
                                            {{ Str::of($i->sex)->limit(10) }}
                                        </span>
                                        <span class="text-muted fw-bold text-muted d-block fs-7">
                                            <b class="p-0 m-0 small text-dark">JOINED:</b>
                                            {{ $i->created_at_text }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($i->program()->id < 1)
                                    <b class="">No imformation yet</b>
                                @else
                                    <b class="text-dark fw-bold  d-block fs-7"
                                        style="color: black">{{ Str::limit($i->program()->program_name, 20) }}</b>
                                    <p class="text-dark d-block fs-6 p-0 m-0">
                                        {{ Str::limit($i->program()->program_award, 20) }}</p>
                                    <b class="fw-bold text-primary d-block fs-7">{{ $i->program()->program_year }}</b>
                                @endif

                            </td>
                            <td class="text-end">
                                <p class="p-0 m-0"><b>COUNTRY:</b> {{ $i->country }}</p>
                                <p class="p-0 m-0"><b>ADDRESS:</b> {{ Str::limit($i->address, 20) }}</p>
                                <p class="p-0 m-0"><b>EMAIL:</b> {{ Str::limit($i->email, 20) }}</p>
                            </td>
                            <td>
                                <div class=" justify-content-end text-right ">
                                    <a href="{{ admin_url("/members/{$i->id}") }}" title="View profile"
                                        class="btn btn-icon btn-bg-light  text-primary  me-1 p-0 px-2 m-0"
                                        style="font-size: 12px;">
                                        <i class="fa fa-eye"></i>
                                        <span>View profile</span>
                                    </a>
                                    <br>
                                    <a href="mailto:{{ $i->email }}" title="View profile"
                                        class="btn btn-icon btn-bg-light  text-primary  me-1 p-0 px-2 m-0"
                                        style="font-size: 12px;">
                                        <i class="fa fa-envelope"></i>
                                        <span>Send email</span>
                                    </a>
                                    <br>
                                    <a href="mailto:{{ $i->email }}" title="View profile"
                                        class="btn btn-icon btn-bg-light  text-primary  me-1 p-0 px-2 m-0"
                                        style="font-size: 12px;">
                                        <i class="fa fa-phone"></i>
                                        <span>Call {{ $i->first_name }}</span>
                                    </a> 


                                </div>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
                <!--end::Table body-->
            </table>
            <!--end::Table-->
        </div>
        <!--end::Table container-->
    </div>
    <!--begin::Body-->
</div>

<?php
use App\Models\Utils;
?><div class="mx-0 mx-md-5 bg-white p-3 p-md-5">
    <div class="d-md-flex justify-content-between">
        <div>
            <h2 class="m-0 p-0 text-dark h3 text-uppercase"><b>{{ $p->name ?? '-' }}'s profile</b></h2>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ $back_link }}" class="btn btn-secondary btn-sm"><i class="fa fa-chevron-left"></i> BACK
                TO ALL MEMBERS</a>
            <a href="mailto:{{ $p->email }}" class="btn btn-warning btn-sm"><i class="fa fa-phone"></i>
                CALL</a>
            <a href="mailto:{{ $p->email }}" class="btn btn-primary btn-sm"><i class="fa fa-envelope"></i> SEND
                EMAIL</a>
        </div>
    </div>

    <hr class="my-3 my-md-4">

    <div class="row">
        <div class="col-3 col-md-2">
            <div class="border border-1 rounded bg-">
                <img class="img-fluid" src="{{ url('storage/' . $p->avatar) }}">
            </div>
        </div>

        <div class="col-9 col-md-5">
            <h3 class="text-uppercase h4 p-0 m-0"><b>BIO DATA</b></h3>
            <hr class="my-1 my-md-3">

            @include('components.detail-item', [
                't' => 'name',
                's' => $p->name,
            ])

            @include('components.detail-item', [
                't' => 'Gender',
                's' => $p->sex,
            ])

            @include('components.detail-item', [
                't' => 'Dob',
                's' => Utils::my_date($p->dob),
            ])


            @include('components.detail-item', [
                't' => 'Nationality',
                's' => $p->country,
            ])

            @include('components.detail-item', [
                't' => 'Current address',
                's' => $p->address,
            ])

            @include('components.detail-item', [
                't' => 'Fluent language',
                's' => $p->language,
            ])

            @include('components.detail-item', [
                't' => 'Current occupation',
                's' => $p->occupation,
            ])

            @include('components.detail-item', [
                't' => 'Email address',
                's' => $p->email,
            ])

            @include('components.detail-item', [
                't' => 'Phone number',
                's' => $p->phone_number,
            ])
        </div>

        <div class="pt-3 pt-md-0 col-md-5">
            <div class=" border border-primary p-3">
                <h3 class="text-uppercase h4 p-0 m-0 text-center"><b>Summary</b></h3>
                <hr class="border-primary mt-3">
                <div style=" font-size: 16px;">
                    <p class="py-0 my-0  "><b>Name:</b> {{ $p->name }}</p>
                    <p class="py-0 my-0 mt-1"><b>Age:</b> {{ $p->dob }}</p>
                    <p class="py-0 my-0 mt-1 "><b>Sex:</b> {{ $p->sex }}</p>
                    <p class="py-0 my-0 mt-1 "><b>Nationality:</b> {{ $p->country }}</p>
                    <p class="py-0 my-0 mt-1 "><b>Email:</b> {{ $p->email }}</p>
                    <p class="py-0 my-0 mt-1 "><b>Phone number:</b> {{ $p->phone_number }}</p>
                    <p class="py-0 my-0 mt-1 "><b>Whatsapp number:</b> {!! $p->whatsapp ?? '-' !!}</p>
                    <p class="py-0 my-0 mt-1 "><b>Facebook username:</b> {!! $p->facebook ?? '-' !!}</p>
                    <p class="py-0 my-0 mt-1 "><b>Twitter:</b> {!! $p->facebook ?? '-' !!}</p>
                    <p class="py-0 my-0 mt-1"><b>Linkedin:</b> {!! $p->linkedin ?? '-' !!}</p>
                    <p class="py-0 my-0 mt-1 "><b>Website:</b> {!! $p->website ?? '-' !!}</p>
                </div>
            </div>
        </div>
    </div>

    <hr class="mt-4 mb-2 border-primary pb-0 mt-md-5 mb-md-5">
    <h3 class="text-uppercase h4 p-0 m-0 text-center"><b>About {{ $p->name }}</b></h3>
    <hr class="m-0 pt-2">
    @if ($p->about != null && strlen($p->about > 10))
        {!! $p->about !!}
    @else
        <div class="alert alert-secondary mt-2 mt-md-3">
            <p>{{ $p->name }} has not written anything about @if ($p->sex == 'Male')
                    himself
                @elseif ($p->sex == 'Female')
                    herself
                @endif.
            </p>
        </div>
    @endif



    <hr class="mt-4 mb-2 border-primary pb-0 mt-md-5 mb-md-5 ">
    <h3 class="text-uppercase h4  m-0 text-center mt-2 "><b>Programs accomplished</b></h3>

    <div class="row mt-2">
        <div class="col-12">
            <table class="table table-striped table-hover">
                <thead class="bg-primary">
                    <tr>
                        <th scope="col">Award</th>
                        <th scope="col">Program</th>
                        <th scope="col">Year of admission</th>
                        <th scope="col">ICT for persons with disabilites Campus</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($p->programs as $sus)
                        <tr>

                            <td>{{ $sus->program_award ?? '-' }}</td>
                            <td>{{ $sus->program_name ?? '-' }}</td>
                            <td>{{ $sus->program_year ?? '-' }}</td>
                            <td>{{ $sus->campus->name ?? '-' }}</td>

                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

</div>

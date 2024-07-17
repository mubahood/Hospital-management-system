@extends('layouts.base-layout')
{{-- account-details --}}
@section('base-content')
    <?php
    $header_data['header_style'] = 2;
    ?>

    <body>
        <main class="page-wrapper">

            @include('layouts.header', $header_data)

            <!-- Page content -->
            <section class="container pt-5">
                <div class="row">

                    @include('layouts.sidebar')

                    <div class="col-md-8 offset-lg-1 pb-5 mb-2 mb-lg-4 pt-md-5 mt-n3 mt-md-0">
                        <div class="ps-md-3 ps-lg-0 mt-md-2 py-md-4">
                            <h1 class="h2 pt-xl-1 pb-3">Account Details</h1>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    @endsection

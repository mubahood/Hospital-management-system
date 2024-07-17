@extends('layouts.base-layout')
{{-- account-details --}}
@section('base-content')
    <?php
    $header_data['header_style'] = 3;
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
                            <form method="POST" action="account-details" class="needs-validation pb-3 pb-lg-4" novalidate>

                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="row pb-2">
                                    <div class="col-sm-6 mb-4">
                                        @include('components.input-text', [
                                            'name' => 'name',
                                            'label' => 'First name',
                                        ])
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        @include('components.input-text', [
                                            'name' => 'last_name',
                                            'label' => 'Last name',
                                        ])
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        @include('components.input-text', [
                                            'name' => 'username',
                                            'type' => 'email',
                                            'label' => 'Email',
                                        ])
                                    </div>
                                    <div class="col-sm-6 mb-4">
                                        @include('components.input-text', [
                                            'name' => 'phone_number',
                                            'type' => 'tel',
                                            'label' => 'Phone number',
                                        ])
                                    </div>
                                    <div class="col-12 mb-4">
                                        @include('components.input-text', [
                                            'name' => 'address',
                                            'label' => 'Address line',
                                        ])
                                    </div>
                                    <div class="col-12 mb-4">
                                        @include('components.input-text', [
                                            'name' => 'bio',
                                            'type' => 'textarea',
                                            'label' => 'Bio <small class="text-muted">(optional)</small>',
                                        ])

                                    </div>
                                </div>
                                <div class="d-flex mb-3">
                                    <button type="reset" class="btn btn-secondary me-3">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>

                        </div>
                    </div>


                </div>
            </section>

        </main>
    @endsection

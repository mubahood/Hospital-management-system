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
            <section class="position-relative h-100 pt-5 pb-4">

                <!-- Sign up form -->
                <div class="container d-flex flex-wrap justify-content-center justify-content-xl-start h-100 pt-5">

                    <div class="w-100 align-self-end pt-1 pt-md-4 pb-4" style="max-width: 526px;">
                        <h1 class="text-center text-xl-start">Register</h1>

                        <p class="lead fs-sm text-dark mt-3 border border-primary p-3 bg-secondary rounded">
                            Dear respected visitor, thank you for your interest in the ICT for Persons With Disabilities. We
                            are seeking to create an national database for for Persons With Disabilities to enhance ways of
                            reaching out and supporting.
                            Please fill out this form to help us get to know you better.
                        </p>
                        <p class="text-center text-xl-start pb-3 mb-3">Already registred an account? <a href="login">Login
                                in here</a></p>
                        <form class="needs-validation" method="POST" action="{{ admin_url('auth/login') }}" novalidate>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    @include('components.input-text', [
                                        'name' => 'name',
                                        'label' => 'Full name',
                                    ])
                                </div>
                                <div class="col-sm-6 mb-3">
                                    @include('components.input-text', [
                                        'name' => 'email',
                                        'type' => 'email',
                                        'label' => 'Email address',
                                    ])
                                </div>
                                <div class="col-12 mb-3">

                                    @include('components.input-text', [
                                        'name' => 'password',
                                        'type' => 'password',
                                        'label' => 'Password',
                                    ])

                                </div>
                                <div class="col-12 mb-4">

                                    @include('components.input-text', [
                                        'name' => 'password_1',
                                        'type' => 'password',
                                        'label' => 'Confirm password',
                                    ])
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary shadow-primary btn-lg w-100">Sign up</button>
                        </form>

                        <div class="w-100 align-self-end  mt-3 mt-md-5">
                            <p class="nav d-block  text-center text-md-start pb-2 pb-lg-0 mb-0">
                                Hand-made with ❤️ by
                                <a class="nav-link d-inline-block p-0" href="https://twitter.com/8TechConsults"
                                    target="_blank" rel="noopener">8Technologies</a>
                            </p>
                        </div>
                    </div>


                    <div class="w-100 align-self-end pt-0 parallax mx-auto d-none d-md-block" style="max-width: 600px;">
                        <!-- Parallax gfx -->
                        <div class="parallax-layer" data-depth="0.1">
                            <img src="assets/img/landing/online-courses/hero/layer01.png" alt="Layer">
                        </div>
                        <div class="parallax-layer" data-depth="0.13">
                            <img src="assets/img/landing/online-courses/hero/layer02.png" alt="Layer">
                        </div>
                        <div class="parallax-layer zindex-5" data-depth="-0.12">
                            <img src="assets/img/landing/online-courses/hero/layer03.png" alt="Layer">
                        </div>
                        <div class="parallax-layer zindex-3" data-depth="0.27">
                            <img src="assets/img/landing/online-courses/hero/layer04.png" alt="Layer">
                        </div>
                        <div class="parallax-layer zindex-1" data-depth="-0.18">
                            <img src="assets/img/landing/online-courses/hero/layer05.png" alt="Layer">
                        </div>
                        <div class="parallax-layer zindex-1" data-depth="0.1">
                            <img src="assets/img/landing/online-courses/hero/layer06.png" alt="Layer">
                        </div>
                    </div>
                </div>



            </section>
        </main>
    @endsection

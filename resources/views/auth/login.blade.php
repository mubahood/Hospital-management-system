@extends('around.layouts.base-layout')
{{-- account-details --}}
@section('base-content')
    <!-- Page wrapper -->
    <main class="page-wrapper">
        <div class="d-lg-flex position-relative h-100">

            <!-- Home button -->
            <a class=" rounded-circle position-absolute top-0 end-0 p-0 mt-3 me-3 mt-sm-4 me-sm-4" href="javascript:;"
                data-bs-toggle="tooltip" data-bs-placement="left"
                title="Powered By {{ env('APP_NAME', 'GlobalHealth') }} (c) 2024"
                aria-label="Powered By {{ env('APP_NAME', 'GlobalHealth') }} (c) 2024"
                style="width: 70px; border-radius: 50%; ">

                <img class="text-center img img-fluid rounded-circle" style="border-radius: 50%;"
                    src="{{ url('assets/img/logo.png') }}" alt="logo">

            </a>

            <!-- Sign in form -->
            <div class="d-flex flex-column align-items-center w-lg-50 h-100 px-3 px-lg-5 pt-5">
                <div class="w-100 mt-auto" style="max-width: 526px;">


                    <h1 class="text-center mb-2 mb-md-5">{{ env('APP_NAME') }}</h1>

                    {{-- if is set $_GET['message'] --}}
                    @if (isset($_GET['message']))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Success!</strong> {{ $_GET['message'] }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <p class="h2 text-primary fs-5 fw-700 pt-2 pt-md-4">Sign in</p>
                    {{--                     <p class="pb-3 mb-3 mb-lg-4">Don't have an account yet?&nbsp;&nbsp;<a
                            href="account-signup.html">Register here!</a></p> --}}
                    <form action="{{ admin_url('auth/login') }}" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="pb-3 mb-3">
                            <div class="position-relative">
                                <i class="ai-mail fs-lg position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                                <input class="form-control form-control-lg ps-5 {!! !$errors->has('username') ?: 'border-danger' !!} " type="email"
                                    name="username" id="username" value="{{ old('username') }}" placeholder="Email address"
                                    required>

                                @if ($errors->has('username'))
                                    @foreach ($errors->get('username') as $message)
                                        <label class="control-label text-danger" for="inputError"><i
                                                class="fa fa-times-circle-o"></i>
                                            <li>{{ $message }}</li>
                                        </label><br>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="position-relative">
                                <i
                                    class="ai-lock-closed fs-lg position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                                <div class="password-toggle">
                                    <input name="password" id="password"
                                        class="form-control form-control-lg ps-5  {!! !$errors->has('password') ?: 'border-danger' !!}" type="password"
                                        placeholder="Password" required>
                                    <label class="password-toggle-btn" aria-label="Show/hide password">
                                        <input class="password-toggle-check" type="checkbox"><span
                                            class="password-toggle-indicator"></span>
                                    </label>
                                </div>
                            </div>
                            @if ($errors->has('password'))
                                @foreach ($errors->get('password') as $message)
                                    <label class="control-label text-danger" for="inputError"><i
                                            class="fa fa-times-circle-o"></i>
                                        <li>{{ $message }}</li>
                                    </label><br>
                                @endforeach
                            @endif
                        </div>
                        <div class="d-flex flex-wrap align-items-center justify-content-between pb-4">
                            <div class="form-check my-1">
                                <input class="form-check-input" type="checkbox" id="keep-signedin">
                                <label class="form-check-label ms-1" for="keep-signedin">Keep me signed in</label>
                            </div>
                            <a class="fs-sm fw-semibold text-decoration-none my-1"
                                href="{{ url('request-password-reset') }}">Forgot password?</a>
                        </div>
                        <button class="btn btn-lg btn-primary w-100 mb-4" type="submit">Sign in</button>


                    </form>
                </div>

                <!-- Copyright -->
                <p class="nav w-100 fs-sm pt-5 mt-auto mb-5" style="max-width: 526px;"><span
                        class="text-body-secondary">&copy; All rights reserved. Made by</span><a
                        class="nav-link d-inline-block p-0 ms-1" href="{{ 'https://8technologies.net' }}" target="_blank"
                        rel="noopener">Eight Tech Consults</a></p>
            </div>


            <!-- Cover image -->
            <div class="w-50 bg-size-cover bg-repeat-0 bg-position-center"
                style="background-image: url({{ url('/storage/images/bg_' . rand(1, 3) . '.jpg') }});"></div>
        </div>
    </main>


    <!-- Back to top button -->
    <a class="btn-scroll-top" href="#top" data-scroll aria-label="Scroll back to top">
        <svg viewBox="0 0 40 40" fill="currentColor" xmlns="../external.html?link=http://www.w3.org/2000/svg">
            <circle cx="20" cy="20" r="19" fill="none" stroke="currentColor" stroke-width="1.5"
                stroke-miterlimit="10"></circle>
        </svg>
        <i class="ai-arrow-up"></i>
    </a>
@endsection

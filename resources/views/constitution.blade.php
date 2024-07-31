@extends('layouts.layout-main')
@section('main-content')
    <!-- Hero -->
    <section class="position-relative pt-5">

        <!-- Background -->
        <div class="position-absolute top-0 start-0 w-100 bg-position-bottom-center bg-size-cover bg-repeat-0"
            style="background-image: url(assets/img/about/hero-bg.svg);">
            <div class="d-lg-none" style="height: 960px;"></div>
            <div class="d-none d-lg-block" style="height: 768px;"></div>
        </div>

        <!-- Content -->
        <div class="container position-relative zindex-5 pt-5 ">
            <div class="row">
                <div class="col-lg-6">

                    <!-- Breadcrumb -->
                    <nav class="pt-md-2 pt-lg-3 pb-0 pb-md-5 mb-xl-4 " aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('') }}"><i class="bx bx-home-alt fs-lg me-1"></i>Home</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ url('about-us') }}">About us</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ env('APP_NAME', 'GlobalHealth') }} -
                                project profile</li>
                        </ol>
                    </nav>
                </div>

            </div>

            <div class="row  justify-content-center mb-4 mb-md-5">

                <div class="col-lg-8 lead text-center">
                    <h1>MaRCCI - project profile</h1>

                    <a href="javascript:;" class="btn btn-primary btn-lg text-uppercase my-5">DOWNLOAD PROFILE <i
                            class="fa fa-file"></i></a>

                </div>
            </div>
        </div>
    </section>
@endsection

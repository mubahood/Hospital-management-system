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
                            <li class="breadcrumb-item active" aria-current="page">Members</li>
                        </ol>
                    </nav>
                </div>

            </div>
        </div>
    </section>


    <!-- Team -->
    <!-- Team -->
    <section class="container pt-xl-2 pb-5 mb-md-3 mb-lg-5">
        <div
            class="d-md-flex align-items-center justify-content-between text-center text-md-start pb-1 pb-lg-0 mb-4 mb-lg-5">
            <h2 class="h1 mb-md-0">Recently joined members</h2>
            <a href="{{ admin_url('') }}" class="btn btn-outline-primary">
                Join now
                <i class="bx bx-right-arrow-alt fs-xl ms-2 me-n1"></i>
            </a>
        </div>
        <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">

            @foreach ($members as $member)
                <!-- Item -->
                <div class="col">
                    <div class="card card-hover border-0 bg-transparent">
                        <div class="position-relative">
                            <img src="{{ $member->avatar }}" class="rounded-3" alt="Dr. Ronald Richards">
                            <div
                                class="card-img-overlay d-flex flex-column align-items-center justify-content-center rounded-3">
                                <span
                                    class="position-absolute top-0 start-0 w-100 h-100 bg-primary opacity-35 rounded-3"></span>
                                <div class="position-relative d-flex zindex-2">
                                    <a href="#" class="btn  btn-secondary btn-facebook btn-sm bg-white me-2">
                                        VIEW FULL PROFILE
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body text-center p-3">
                            <h3 class="fs-lg fw-semibold pt-0 mb-2">{{ $member->name }}</h3>
                            <p class="fs-sm mb-2">{{ $member->campus->name }}</p>
                            <div class="d-flex align-items-center justify-content-center">
                                <span class="fs-sm text-muted">joined {{ $member->created_at_text }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </section>
@endsection

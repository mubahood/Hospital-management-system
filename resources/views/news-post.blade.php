<?php
use App\Models\PostCategory;
use App\Models\NewsPost;
use App\Models\Utils;
if (!isset($header_style)) {
    $header_style = 11;
}

?>@extends('layouts.layout-main')
@section('main-content')
    <!-- Breadcrumb -->
    <nav class="container mt-5   pt-5" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ url('') }}"><i class="bx bx-home-alt fs-lg me-1"></i>Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ url('news') }}">News</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($p->title, 20) }}</li>
        </ol>
    </nav>


    <!-- Post title + Meta  -->
    <section class="container mt-4 pt-lg-2 pb-3">
        <h1 class="pb-3" style="max-width: 970px;">{{ $p->title }}</h1>
        <div class="d-flex flex-md-row flex-column align-items-md-center justify-content-md-between mb-3">
            <div class="d-flex align-items-center flex-wrap text-muted mb-md-0 mb-4">
                <div class="fs-xs border-end pe-3 me-3 mb-2">
                    <span
                        class="badge bg-faded-primary text-primary fs-base">{{ Str::limit($p->category->name, 20) }}</span>
                </div>
                <div class="fs-sm border-end pe-3 me-3 mb-2">{{ Utils::my_date_time($p->created_at) }}</div>
                <div class="d-flex mb-2">
                    <div class="d-flex align-items-center me-3">
                        <i class="bx bx-like fs-base me-1"></i>
                        <span class="fs-sm">{{ rand(10, 1000) }}</span>
                    </div>
                    <div class="d-flex align-items-center me-3">
                        <i class="bx bx-comment fs-base me-1"></i>
                        <span class="fs-sm">{{ rand(10, 100) }}</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bx bx-share-alt fs-base me-1"></i>
                        <span class="fs-sm">{{ rand(10, 50) }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center position-relative ps-md-3 pe-lg-5 mb-2">
                <img src="{{ $p->created_by->avatar }}" class="rounded-circle" width="60" alt="Avatar">
                <div class="ps-3">
                    <h6 class="mb-1">Author</h6>
                    <a href="{{ url('admin/members/' . $p->created_by->id) }}"
                        class="fw-semibold stretched-link">{{ $p->created_by->name }}</a>
                </div>
            </div>
        </div>
    </section>


    <!-- Post image (parallax) -->
    <div class="jarallax d-lg-none mb-lg-5 mb-4" data-jarallax data-speed="0.35"
        style="height: 36.45vw; min-height: 300px;">
        <div class="jarallax-img" style="background-image: url({{ url('storage/' . $p->photo) }});"></div>
    </div>


    <!-- Post content + Sharing -->
    <section class="container mb-5 pt-4 pt-lg-0 pb-2 py-mg-4">
        <div class="row gy-4">

            <!-- Content -->
            <div class="col-lg-9">

                <img class="img-fluid rounded mb-3 d-none d-lg-block" src="{{ url('storage/' . $p->photo) }}"
                    alt="">

                <h3 class="h5 mb-4 pb-2 fw-medium">{{ $p->description }}</h3>

                {!! $p->details !!}
            </div>

            <!-- Sharing -->
            <div class="col-lg-3 position-relative">
                <div class="sticky-top ms-xl-5 ms-lg-4 ps-xxl-4" style="top: 105px !important;">
                    <span class="d-block mb-3">5 min read</span>
                    <h6>Share this post:</h6>
                    <div class="mb-4 pb-lg-3">
                        <a href="javascript:;" class="btn btn-icon btn-secondary btn-linkedin me-2 mb-2">
                            <i class="bx bxl-linkedin"></i>
                        </a>
                        <a href="javascript:;" class="btn btn-icon btn-secondary btn-facebook me-2 mb-2">
                            <i class="bx bxl-facebook"></i>
                        </a>
                        <a href="javascript:;" class="btn btn-icon btn-secondary btn-twitter me-2 mb-2">
                            <i class="bx bxl-twitter"></i>
                        </a>
                        <a href="javascript:;" class="btn btn-icon btn-secondary btn-instagram me-2 mb-2">
                            <i class="bx bxl-instagram"></i>
                        </a>
                    </div>
                    <button class="btn btn-lg btn-outline-secondary">
                        <i class="bx bx-like me-2 lead"></i>
                        Like it
                        <span class="badge bg-primary shadow-primary mt-n1 ms-3">8</span>
                    </button>
                </div>
            </div>
        </div>
    </section>




    <!-- Related articles (Slider below lg breakpoint) -->
    <section class="container mb-5 pt-md-4">
        <div class="d-flex flex-sm-row flex-column align-items-center justify-content-between mb-4 pb-1 pb-md-3">
            <h2 class="h1 mb-sm-0">Related Articles</h2>
            <a href="{{ url("news") }}" class="btn btn-lg btn-outline-primary ms-4">
                All posts
                <i class="bx bx-right-arrow-alt ms-1 me-n1 lh-1 lead"></i>
            </a>
        </div>

        <div class="swiper mx-n2"
            data-swiper-options='{
          "slidesPerView": 1,
          "spaceBetween": 8,
          "pagination": {
            "el": ".swiper-pagination",
            "clickable": true
          },
          "breakpoints": {
            "500": {
              "slidesPerView": 2
            },
            "1000": {
              "slidesPerView": 3
            }
          }
        }'>
            <div class="swiper-wrapper">

                @foreach ($posts as $post)
                    <!-- Item -->
                    <div class="swiper-slide h-auto pb-3">
                        <article class="card border-0 shadow-sm h-100 mx-2">
                            <div class="  position-relative bg-position-center bg-repeat-0 bg-size-cover"
                                style="background-image: url({{ url('storage/' . $post->photo) }}); min-height: 15rem;">
                                <a href="{{ url('news/' . $post->id) }}"
                                    class="position-absolute top-0 start-0 w-100 h-100" aria-label="Read more"></a>
                                <a href="{{ url('news/' . $post->id) }}"
                                    class="btn btn-icon btn-light bg-white border-white btn-sm rounded-circle position-absolute top-0 end-0 zindex-5 me-3 mt-3"
                                    data-bs-toggle="tooltip" data-bs-placement="left" title="Read later">
                                    <i class="bx bx-bookmark"></i>
                                </a>
                            </div>
                            <div class="card-body pb-4">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <a href="#"
                                        class="badge fs-sm text-nav bg-secondary text-decoration-none">{{ $post->category->name }}</a>
                                    <span class="fs-sm text-muted">{{ Utils::my_date($post->created_at) }}</span>
                                </div>
                                <h3 class="h5 mb-0">
                                    <a href="{{ url('news/' . $post->id) }}" title="{{ $post->title }}">
                                        {{ Str::limit($post->title, 60) }}</a>
                                </h3>
                            </div>
                            <div class="card-footer py-4">
                                <a href="{{ url('admin/members/' . $post->created_by->id) }}"
                                    class="d-flex align-items-center fw-bold text-dark text-decoration-none">
                                    <img src="{{ $post->created_by->avatar }}" class="rounded-circle me-3"
                                        width="48" alt="Avatar">
                                    {{ $post->created_by->name }}
                                </a>
                            </div>
                        </article>
                    </div>
                @endforeach

            </div>

            <!-- Pagination (bullets) -->
            <div class="swiper-pagination position-relative pt-2 pt-sm-3 mt-4"></div>
        </div>
    </section>
@endsection

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
    <nav class="container mt-lg-4 pt-5" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 pt-5">
            <li class="breadcrumb-item">
                <a href="{{ url('') }}"><i class="bx bx-home-alt fs-lg me-1"></i>Home</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">News</li>
        </ol>
    </nav>


    <!-- Page content -->
    <section class="container mt-4 mb-lg-5 pt-lg-2 pb-5">

        <!-- Page title + Layout switcher + Search form -->
        <div class="row align-items-end gy-3 mb-4 pb-lg-3 pb-1">
            <div class="col-lg-5 col-md-4">
                <h1 class="mb-2 mb-md-0">News</h1>
            </div>
            <div class="col-lg-7 col-md-8">
                <div class="row gy-2">
                    <div class="col-lg-5 col-sm-6">
                        <div class="d-flex align-items-center">
                            <div class="d-flex align-items-center me-sm-4 me-3">
                                <a href="{{ url('news') }}" class="nav-link me-2 p-0 active">
                                    <i class="bx bx-list-ul fs-4"></i>
                                </a>
                                <a href="{{ url('news') }}" class="nav-link p-0">
                                    <i class="bx bx-grid-alt fs-4"></i>
                                </a>
                            </div>
                            <select class="form-select">
                                <option>All categories</option>
                                @foreach (PostCategory::all() as $cat)
                                    <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-7 col-sm-6">
                        <div class="input-group">
                            <input type="text" class="form-control pe-5 rounded" placeholder="Search the news...">
                            <i
                                class="bx bx-search position-absolute top-50 end-0 translate-middle-y me-3 zindex-5 fs-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @foreach (NewsPost::all() as $post)
            <!-- Item -->
            <article class="card border-0 shadow-sm overflow-hidden mb-4">
                <div class="row g-0">
                    <div class="col-sm-4 position-relative bg-position-center bg-repeat-0 bg-size-cover"
                        style="background-image: url({{ url('storage/' . $post->photo) }}); min-height: 15rem;">
                        <a href="{{ url('news/' . $post->id) }}" class="position-absolute top-0 start-0 w-100 h-100"
                            aria-label="Read more"></a>
                        <a href="{{ url('news/' . $post->id) }}"
                            class="btn btn-icon btn-light bg-white border-white btn-sm rounded-circle position-absolute top-0 end-0 zindex-5 me-3 mt-3"
                            data-bs-toggle="tooltip" data-bs-placement="left" title="Read later">
                            <i class="bx bx-bookmark"></i>
                        </a>
                    </div>
                    <div class="col-sm-8">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <a href="news" class="badge fs-sm text-nav bg-secondary text-decoration-none">{{   $post->category->name }}</a>
                                <span class="fs-sm text-muted border-start ps-3 ms-3">{{ Utils::my_date($post->created_at) }}</span>
                            </div>
                            <h3 class="h4">
                                <a href="{{ url('news/' . $post->id) }}" title="{{ $post->title }}">
                                    {{ Str::limit($post->title, 60) }}
                                </a>
                            </h3>
                            <p> {{ Str::limit($post->description, 300) }}</p>
                            <hr class="my-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <a href="{{ url('admin/members/'.$post->created_by->id) }}"
                                    class="d-flex align-items-center fw-bold text-dark text-decoration-none me-3">
                                    <img src="{{   $post->created_by->avatar  }}" class="rounded-circle me-3" width="48"
                                        alt="Avatar">
                                        {{   $post->created_by->name }}
                                </a>
                                <div class="d-flex align-items-center text-muted">
                                    <div class="d-flex align-items-center me-3">
                                        <i class="bx bx-like fs-lg me-1"></i>
                                        <span class="fs-sm">8</span>
                                    </div>
                                    <div class="d-flex align-items-center me-3">
                                        <i class="bx bx-comment fs-lg me-1"></i>
                                        <span class="fs-sm">7</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-share-alt fs-lg me-1"></i>
                                        <span class="fs-sm">4</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach


    </section>

 
@endsection

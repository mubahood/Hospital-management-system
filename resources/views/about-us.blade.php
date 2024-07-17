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
        <div class="container position-relative zindex-5 pt-5">
            <div class="row">
                <div class="col-lg-6">

                    <!-- Breadcrumb -->
                    <nav class="pt-md-2 pt-lg-3 pb-4 pb-md-5 mb-xl-4" aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ url('') }}"><i class="bx bx-home-alt fs-lg me-1"></i>Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">About us</li>
                        </ol>
                    </nav>

                    <!-- Text -->
                    <h1 class="pb-2 pb-md-3">About ICT for Persons with disabilites</h1>
                    <p class="fs-xl pb-4 mb-1 mb-md-2 mb-lg-3" style="max-width: 526px;">


                        Uganda Communications Commission (UCC), through The Uganda Communications Universal Service and
                        Access Fund (UCUSAF), which is a Universal Service Fund (USF) for communications in Uganda, launched
                        a call for business plan proposals to establish collaboration on the implementation of key
                        activities under a general thematic area of addressing digital inclusiveness of Persons with
                        Disabilities (PWD) ICT. <br><br>
                        Therefore, the UCUSAF III program set aside resources to be utilized under a
                        collaborative grant framework between UCC/UCUSAF and a suitable partner, some of these funds are
                        available within the operational budget of the financial year 2021/2022 to implement activities
                        related to addressing digital inclusiveness of Persons with Disabilities, by Enhancing Knowledge
                        Management, ICT Adaption, Digital Skills and Access to E-Services for Persons with Disabilities.


                    </p>
                    <h2 class="  mb-2 h2">Overall project objective</h2>
                    <b class="fs-xl pb-4 mb-1 mb-md-2 mb-lg-3">
                        "To promote digital inclusiveness for Persons with Disabilities through the use of ICT enabled
                        technologies."
                    </b>
                </div>

                <!-- Images -->
                <div class="col-lg-6 mt-xl-3 pt-5 pt-lg-4">
                    <div class="row row-cols-2 gx-3 gx-lg-4">
                        <div class="col pt-lg-5 mt-lg-1">
                            <img src="assets/images/group/1.jpg" class="d-block rounded-3 mb-3 mb-lg-4" alt="Image">
                            <img src="assets/images/group/2.jpg" class="d-block rounded-3" alt="Image">
                            <img src="assets/images/group/7.jpg" class="d-block rounded-3 mt-4" alt="Image">
                            <img src="assets/images/group/8.jpg" class="d-block rounded-3 mt-4" alt="Image">
                        </div>
                        <div class="col">
                            <img src="assets/images/group/10.jpg" class="d-block rounded-3 mb-3 mb-lg-4" alt="Image">
                            <img src="assets/images/group/4.jpg" class="d-block rounded-3" alt="Image">
                            <img src="assets/images/group/5.jpg" class="d-block rounded-3 mt-4" alt="Image">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="container mt-3 mt-md-5">
        <p class="lead">The project is anchored on three key <b>specific objectives</b> which include;</p>
    </div>
    <!-- Benefits (features) -->
    <div class="container  mb-5 pt-lg-5 bg-secondary rounded my-3 my-md-5" id="benefits">


        <div class="swiper pt-3"
            data-swiper-options='{
                  "slidesPerView": 1,
                  "pagination": {
                    "el": ".swiper-pagination",
                    "clickable": true
                  },
                  "breakpoints": {
                    "500": {
                      "slidesPerView": 2
                    },
                    "991": {
                      "slidesPerView": 3
                    }
                  }
                }'>
            <div class="swiper-wrapper pt-4">


                <!-- Item -->

                <div class="swiper-slide border-end-lg px-2">
                    <div class="text-center">
                        <img src="assets/img/landing/digital-agency/icons/award.svg" width="48" alt="Award icon"
                            class="d-block mb-4 mx-auto">
                        <p class="mx-auto lead" style="max-width: 336px;">To develop and operationalize a National digital
                            observatory (database) for PWD.</p>
                    </div>
                </div>

                <!-- Item -->
                <div class="swiper-slide border-end-lg px-2">
                    <div class="text-center">
                        <img src="assets/img/landing/digital-agency/icons/idea.svg" width="48" alt="Bulb icon"
                            class="d-block mb-4 mx-auto">

                        <p class="mx-auto  lead" style="max-width: 336px;">To establish baseline of PWD digital
                            inclusiveness in e-services access, technology, content, and information needs for various
                            categories of PWD across the country..<br><br><br><br></p>
                    </div>
                </div>


                <!-- Item -->
                <div class="swiper-slide px-2">
                    <div class="text-center">
                        <img src="assets/img/landing/digital-agency/icons/team.svg" width="48" alt="Team icon"
                            class="d-block mb-4 mx-auto">
                        <p class="mx-auto lead" style="max-width: 336px;">To mobilize and create awareness of ICT access and
                            potential among various categories of PWD.</p>
                    </div>
                </div>
            </div>

            <!-- Pagination (bullets) -->
            <div class="swiper-pagination position-relative pt-2 pt-sm-3 mt-4"></div>
        </div>
    </div>

    <!-- Video showreel -->
    <section class="container text-center pb-5 mt-n2 mt-md-0 mb-md-2 mb-lg-4">
        <div class="position-relative rounded-3 overflow-hidden mb-lg-3">
            <div
                class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center zindex-5">
                <a href="../external.html?link=https://youtu.be/iEn8feA8MD4"
                    class="btn btn-video btn-icon btn-xl stretched-link bg-white" data-bs-toggle="video">
                    <i class="bx bx-play"></i>
                </a>
            </div>
            <span class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-35"></span>
            <img src="assets/images/drake-training.jpg" alt="Cover image">
        </div>
    </section>




    </main>
@endsection

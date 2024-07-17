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
                            <li class="breadcrumb-item active" aria-current="page">Project objectives</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <h1>Project objectives</h1> 

            <p class="text-center mt-2"><b class="fs-xl lead pb-4 mb-1 mb-md-2 mb-lg-3">
                "To promote digital inclusiveness for Persons with Disabilities through the use of ICT enabled
                technologies."
            </b></p>
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



 
@endsection

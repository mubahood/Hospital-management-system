@extends('layouts.layout-main')
@section('main-content')
    <?php
    use App\Models\PostCategory;
    use App\Models\NewsPost;
    use App\Models\Utils;
    if (!isset($header_style)) {
        $header_style = 11;
    }
    
    ?>
    <!-- Hero -->
    <section class="position-relative pt-md-3 pt-lg-5 mb-md-3 mb-lg-5">
        <div class="container position-relative zindex-5 pt-5">
            <div class="row mt-4 pt-5">
                <div class="col-xl-4 col-lg-5 text-center text-lg-start pb-3 pb-md-4 pb-lg-0">
                    <h1 class="fs-xl text-uppercase">Welcome to</h1>
                    <h2 class="display-4 pb-md-2 pb-lg-1" style="font-size: 3rem; line-height: 3rem;">Islamic University in
                        Uganda - Alumni Association</h2>
                    <p class="fs-lg">Haven't joined yet? <a href="{{ admin_url() }}" class="fw-medium">Click here.</a></p>
                </div>
                <div class="col-xl-5 col-lg-6 offset-xl-1 position-relative zindex-5 mb-5 mb-lg-0">
                    <div class="rellax card   shadow-primary py-2 p-sm-2 p-lg-4" 
                        style="background-color: rgba(12, 118, 14, 0.7); border: 5px solid rgb(1, 62, 1)!important;"
                    data-rellax-speed="-1"
                        data-disable-parallax-down="lg">
                        <div class="card-body p-lg-2">
                            <h2 class="text-white pb-1 pb-md-3 text-dark">Chairman’s Message</h2>
                            <p class="fs-lg text-white pb-2 pb-md-0 mb-4 mb-md-5  text-dark">
                                It is my distinct honour and privilege to welcome you to the ICT for persons with disabilites Association
                                website.
                                If you are a graduate of ICT for persons with disabilites you are yet to become a member of this Association. <br> I
                                earnestly invite you to do so - it's easy and inexpensive...</p>
                            <a href="{{ url('chairperson-message') }}" class="btn btn-light btn-lg">
                                Read full message
                                <i class="bx bx-right-arrow-alt lh-1 fs-4 ms-2 me-n2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-none d-lg-block" style="margin-top: -165px;"></div>
            <div class="row align-items-end">
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="assets/images/chairperson.JPG" class="rellax rounded-3" alt="Image"
                        data-rellax-speed="1.35" data-disable-parallax-down="md">
                </div>
                <div class="col-lg-6 d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <div class="d-flex align-items-center ps-xl-5 mb-4 mb-md-0">
                        <div class="btn btn-icon btn-secondary btn-lg pe-none rounded d-lg-none d-xl-inline-flex">
                            <i class="bx bx-time text-primary fs-3"></i>
                        </div>
                        <ul class="list-unstyled ps-3 ps-lg-0 ps-xl-3 mb-0">
                            <li><strong class="text-dark">UPCOMING EVENT:</strong> ICT for persons with disabilites Dinner</li>
                            <li><strong class="text-dark">10th Feb, 2023:</strong> ICT for persons with disabilites's campus</li>
                        </ul>
                    </div>
                    <a href="{{ admin_url('event-bookings/create?event=1') }}"
                        class="btn btn-primary btn-lg shadow-primary">BOOK A TICKET</a>
                </div>
            </div>
        </div>
        <div class="d-none d-lg-block position-absolute top-0 end-0 w-50 d-flex flex-column ps-3"
            style="height: calc(100% - 108px);">
            <div class="w-100 h-100 overflow-hidden bg-position-center bg-repeat-0 bg-size-cover"
                style="background-image: url(assets/images/group/{{ rand(1, 10) }}.jpg); border-bottom-left-radius: .5rem;">
            </div>
        </div>
    </section>




    <!-- Video showreel -->
    <section class="container text-center pb-5 mb-3 mb-md-4 mb-lg-5 mt-md-5">
        <h2 class="h1 pt-1 mb-4">About ICT for persons with disabilites Association</h2>
        <div class="row justify-content-center mb-md-2 mb-lg-5">
            <div class="col-lg-6 col-md-8">
                <p class="fs-lg text-lead ">The Islamic University In Uganda Alumni Association (ICT for persons with disabilites) was formally
                    registered with the Uganda Registration Services Bureau (URSB) on August 12th, 2020 Kampala.</p>
                <p class="fs-lg text-lead mt-2">
                    ICT for persons with disabilites seeks to create a platform for discussion and practical solutions that benefit the members and
                    our great University.</p>
                <p class="fs-lg text-lead mt-2">Furthermore, promote fellowship among ICT for persons with disabilites, assist members to
                    develop careers prospects and maintain a database of ICT for persons with disabilites at national and international levels.
                </p>
                <p class="fs-lg text-lead mt-2 ">As a network that unites ICT for persons with disabilites under one umbrella, we look forward to
                    promoting the interests, welfare and educational aims of ICT for persons with disabilites the University.</p>

                <p class="fs-lg">Read more about ICT for persons with disabilites <a href="{{ url('about-us') }}" class="fw-medium">Click here</a>
                </p>
            </div>


            <!-- Benefits (features) -->
            <div class="mt-3 mb-5 pt-lg-5 bg-secondary rounded" id="benefits" style="border: solid 3px rgb(3, 68, 3);">
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
                                <img src="assets/img/landing/digital-agency/icons/idea.svg" width="48" alt="Bulb icon"
                                    class="d-block mb-4 mx-auto">
                                <h4 class="mb-2 pb-1">Our Vision</h4>
                                <p class="mx-auto" style="max-width: 336px;">Unity and Development.<br><br><br><br></p>
                            </div>
                        </div>

                        <!-- Item -->
                        <div class="swiper-slide border-end-lg px-2">
                            <div class="text-center">
                                <img src="assets/img/landing/digital-agency/icons/award.svg" width="48" alt="Award icon"
                                    class="d-block mb-4 mx-auto">
                                <h4 class="mb-2 pb-1">Our Mission</h4>
                                <p class="mx-auto" style="max-width: 336px;">To create fellowship among ICT for persons with disabilites under
                                    one umbrella and offer practical solutions that benefit members and the Alma mater
                                    through rigorous networking.</p>
                            </div>
                        </div>

                        <!-- Item -->
                        <div class="swiper-slide px-2">
                            <div class="text-center">
                                <img src="assets/img/landing/digital-agency/icons/team.svg" width="48" alt="Team icon"
                                    class="d-block mb-4 mx-auto">
                                <h4 class="mb-2 pb-1">Our Core Values</h4>
                                <p class="mx-auto" style="max-width: 336px;">Integrity, Diversity, Creativity & coexistence.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination (bullets) -->
                    <div class="swiper-pagination position-relative pt-2 pt-sm-3 mt-4"></div>
                </div>
            </div>

        </div>
        <div class="position-relative rounded-3 overflow-hidden mb-lg-3">
            <div
                class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center zindex-5">
                <a href="../external.html?link=https://youtu.be/yGxEamdlHB4"
                    class="btn btn-video btn-icon btn-xl stretched-link bg-white" data-bs-toggle="video">
                    <i class="bx bx-play"></i>
                </a>
            </div>
            <span class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-35"></span>
            <img src="assets/images/ICT for persons with disabilites.jpg" alt="Cover image">
        </div>
    </section>


















    <!-- Services -->
    <section class="container pb-5 mb-md-2 mb-lg-5">
        <div class="row">
            <div class="col-lg-4 text-center text-lg-start pb-3 pb-lg-0 mb-4 mb-lg-0">
                <h2 class="h1 mb-lg-4">Discover ICT for persons with disabilites Services</h2>
                <p class="pb-4 mb-0 mb-lg-3">
                    ICT for persons with disabilites Membership enables you to enjoy being a part of the ICT for persons with disabilites community and related fellowship
                    in various forms. Take advantage of the opportunity that this association offers to all Alumni of ICT for persons with disabilites dear alma mater, to give our loving attention and support that she deserves for achieving greater
                    glory and world class status.</p>
                <a href="{{ url('about-us') }}" class="btn btn-primary shadow-primary btn-lg">ABOUT ICT for persons with disabilites</a>
            </div>
            <div class="col-xl-7 col-lg-8 offset-xl-1">
                <div class="row row-cols-1 row-cols-md-2">
                    <div class="col">
                        <div class="card card-hover bg-secondary border-0 mb-4">
                            <div class="card-body d-flex align-items-start">
                                <div class="flex-shrink-0 bg-light rounded-3 p-0">
                                    <img src="assets/images/icon-check.png" width="50" alt="Icon">
                                </div>
                                <div class="ps-4">
                                    <h3 class="h5 pb-2 mb-1">RECONNECT</h3>
                                    <p class="pb-2 mb-1 ">Reconnect with your friends, classmates, OBs and OGs.</p>
                                    <a href="{{ admin_url('') }}" class="btn btn-link stretched-link px-0">
                                        Join now
                                        <i class="bx bx-right-arrow-alt fs-xl ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card card-hover bg-secondary border-0 mb-4">
                            <div class="card-body d-flex align-items-start">
                                <div class="flex-shrink-0 bg-light rounded-3 p-0">
                                    <img src="assets/images/icon-check.png" width="50" alt="Icon">
                                </div>
                                <div class="ps-4">
                                    <h3 class="h5 pb-2 mb-1">PROFILE</h3>
                                    <p class="pb-2 mb-1">Create your profile and showcase your skills, capabilities,
                                        qualifications and experience.</p>
                                    <a href="{{ admin_url('') }}" class="btn btn-link stretched-link px-0">
                                        Join now
                                        <i class="bx bx-right-arrow-alt fs-xl ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card card-hover bg-secondary border-0 mb-4">
                            <div class="card-body d-flex align-items-start">
                                <div class="flex-shrink-0 bg-light rounded-3 p-0">
                                    <img src="assets/images/icon-check.png" width="50" alt="Icon">
                                </div>
                                <div class="ps-4">
                                    <h3 class="h5 pb-2 mb-1">GIVE BACK</h3>
                                    <p class="pb-2 mb-1">Give Back to your alma mater and your alumni association by
                                        participating in a fundraising event and donations.



                                    </p>
                                    <a href="{{ admin_url('') }}" class="btn btn-link stretched-link px-0">
                                        Join now
                                        <i class="bx bx-right-arrow-alt fs-xl ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card card-hover bg-secondary border-0 mb-4">
                            <div class="card-body d-flex align-items-start">
                                <div class="flex-shrink-0 bg-light rounded-3 p-0">
                                    <img src="assets/images/icon-check.png" width="50" alt="Icon">
                                </div>
                                <div class="ps-4">
                                    <h3 class="h5 pb-2 mb-1">GET IN TOUCH</h3>
                                    <p class="pb-2 mb-1">Stay updated with feeds about scholarships, job openings and
                                        announcements.</p>
                                    <a href="{{ admin_url('') }}" class="btn btn-link stretched-link px-0">
                                        Join now
                                        <i class="bx bx-right-arrow-alt fs-xl ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card card-hover bg-secondary border-0 mb-4">
                            <div class="card-body d-flex align-items-start">
                                <div class="flex-shrink-0 bg-light rounded-3 p-0">
                                    <img src="assets/images/icon-check.png" width="50" alt="Icon">
                                </div>
                                <div class="ps-4">
                                    <h3 class="h5 pb-2 mb-1">INQUIRIES</h3>
                                    <p class="pb-2 mb-1">Make inquiry for job openings, adverts and advance your
                                        professional network.</p>
                                    <a href="{{ admin_url('') }}" class="btn btn-link stretched-link px-0">
                                        Join now
                                        <i class="bx bx-right-arrow-alt fs-xl ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card card-hover bg-secondary border-0 mb-4">
                            <div class="card-body d-flex align-items-start">
                                <div class="flex-shrink-0 bg-light rounded-3 p-0">
                                    <img src="assets/images/icon-check.png" width="50" alt="Icon">
                                </div>
                                <div class="ps-4">
                                    <h3 class="h5 pb-2 mb-1">MENTORSHIP</h3>
                                    <p class="pb-2 mb-1">Mentor others by telling your success story or by reading other
                                        Alumni success stories.

                                    </p>
                                    <a href="{{ admin_url('') }}" class="btn btn-link stretched-link px-0">
                                        Join now
                                        <i class="bx bx-right-arrow-alt fs-xl ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>




    <!-- Team -->
    <section class="container pt-xl-2 pb-5 mb-md-3 mb-lg-5">
        <div
            class="d-md-flex align-items-center justify-content-between text-center text-md-start pb-1 pb-lg-0 mb-4 mb-lg-5">
            <h2 class="h1 mb-md-0">Forward-Thinking People Like You Already Joined</h2>
            <a href="{{ admin_url('members') }}" class="btn btn-outline-primary">
                View All Members
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
                                    <a href="{{ url('admin/members/' . $member->id) }}"
                                        class="btn  btn-secondary btn-facebook btn-sm bg-white me-2">
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





    <!-- Testimonials (Carousel) -->
    <section class="bg-secondary py-5 mb-2 mb-md-4 mb-lg-5">
        <div class="container pt-lg-4 mt-1">
            <h2 class="h1 text-center pb-3 pb-md-4 pb-xl-5">ICT for persons with disabilites - Profiles</h2>
        </div>
        <div class="pb-lg-3 px-2 px-sm-0">
            <div class="swiper"
                data-swiper-options='{
            "slidesPerView": 1,
            "centeredSlides": true,
            "spaceBetween": 8,
            "loop": true,
            "pagination": {
              "el": ".swiper-pagination",
              "clickable": true
            },
            "breakpoints": {
              "500": {
                "slidesPerView": 2,
                "spaceBetween": 24
              },
              "1000": {
                "slidesPerView": 4,
                "spaceBetween": 24
              },
              "1500": {
                "slidesPerView": 6,
                "spaceBetween": 24
              }
            }
          }'>

                <div class="swiper-wrapper">
                    @foreach ($profiles as $profile)
                        <!-- Item -->
                        <div class="swiper-slide h-auto pt-4">
                            <figure class="d-flex flex-column h-100 px-2 px-sm-0 mb-0">
                                <div class="card h-100 position-relative border-0 shadow-sm pt-4">
                                    <span
                                        class="btn btn-icon btn-primary shadow-primary pe-none position-absolute top-0 start-0 translate-middle-y ms-4">
                                        <i class="bx bxs-quote-left"></i>
                                    </span>
                                    <blockquote class="card-body pb-3 mb-0">
                                        <p class="mb-0">{{ Str::limit($profile->intro, 150) }}</p>
                                    </blockquote>
                                    <div class="card-footer border-0 text-nowrap pt-0">
                                        <span>Joined</span>
                                        <span class=" text-primary">{{ $member->created_at_text }}</span>
                                    </div>
                                </div>
                                <figcaption class="d-flex align-items-center ps-4 pt-4">
                                    <img src="{{ $member->avatar }}" width="48" class="rounded-circle"
                                        alt="{{ $member->name }}">
                                    <div class="ps-3">
                                        <h6 class="fs-sm fw-semibold mb-0">{{ $member->name }}</h6>
                                        <span class="fs-xs text-muted">{{ $member->campus->name }}</span>
                                    </div>
                                </figcaption>
                            </figure>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination (bullets) -->
                <div class="swiper-pagination position-relative pt-1 pt-sm-3 mt-5"></div>
            </div>
        </div>
    </section>






    <!-- Latest news -->
    <section class="container pt-4 pb-2 mb-1 mb-md-0 ">
        <h2 class="h1 text-center pt-1 pb-4 mb-1 mb-lg-3">Our Latest News</h2>
        <div class="position-relative px-xl-5">

            <!-- Slider prev/next buttons -->
            <button type="button" id="prev-news"
                class="btn btn-prev btn-icon btn-sm position-absolute top-50 start-0 translate-middle-y d-none d-xl-inline-flex">
                <i class="bx bx-chevron-left"></i>
            </button>
            <button type="button" id="next-news"
                class="btn btn-next btn-icon btn-sm position-absolute top-50 end-0 translate-middle-y d-none d-xl-inline-flex">
                <i class="bx bx-chevron-right"></i>
            </button>

            <!-- Slider -->
            <div class="px-xl-2">
                <div class="swiper mx-n2"
                    data-swiper-options='{
              "slidesPerView": 1,
              "loop": true,
              "pagination": {
                "el": ".swiper-pagination",
                "clickable": true
              },
              "navigation": {
                "prevEl": "#prev-news",
                "nextEl": "#next-news"
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
                                            class="position-absolute top-0 start-0 w-100 h-100"
                                            aria-label="Read more"></a>
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
            </div>
        </div>
    </section>



    <!-- Location -->
    <section class="container py-5">
        <div class="row mt-lg-3 pt-1 pt-md-4 pt-lg-5">
            <div class="col-xl-3 col-md-4 text-center text-md-start">
                <h2 class="h1 mb-4">ICT for persons with disabilites Grand Dinner - 2023</h2>
                <h3 class="h4">Venue</h3>
                <div class="d-none d-md-block text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="118" height="118" fill="none">
                        <g clip-path="url(#A)">
                            <path
                                d="M116.912 76.527c-4.963-3.081-9.413-6.675-13.52-10.782-1.37-1.369-3.766-.343-3.766 1.54 0 1.54-.171 3.081-.171 4.621-14.89 2.739-29.78 3.936-45.013 4.108-12.836.342-30.123 1.712-41.761-4.45-8.9-4.792-10.269-15.917-6.504-24.475 1.54-3.594 4.279-6.504 7.702-8.557 3.936-2.396 7.873-1.027 11.981-2.054.513-.171.685-.856.342-1.369-6.333-6.675-17.457 1.027-21.565 6.504-5.819 7.702-6.161 18.998-1.027 27.042 7.531 11.981 25.501 11.125 37.653 11.467 19.169.685 39.365.171 58.192-4.108 0 1.712.171 3.252.685 4.963 0 .342.171.513.342.685-1.369 1.198-.171 4.279 2.225 3.765 4.963-1.027 9.927-2.568 14.548-4.792 1.198-1.026.856-3.251-.343-4.107zm-13.178-4.45c2.396 2.054 4.792 4.108 7.531 5.99-2.396 1.027-4.964 1.712-7.531 2.396v-.685c-.514-2.567-.342-5.135 0-7.702z"
                                fill="currentColor" />
                        </g>
                        <defs>
                            <clipPath id="A">
                                <path fill="#fff" d="M0 0h118v118H0z" />
                            </clipPath>
                        </defs>
                    </svg>
                </div>
            </div>
            <div class="col-xl-9 col-md-8">
                <img src="assets/images/ICT for persons with disabilites-min.jpg" class="rounded-3" width="952" alt="Venue">
            </div>
        </div>
        <div class="row mb-lg-3 pb-1 pb-md-4 pb-lg-5 mt-4 mt-sm-n5">
            <div class="col-lg-4 col-md-5 offset-md-6 offset-lg-7 mt-md-n5">
                <div class="gallery mt-md-n4 mx-auto" style="max-width: 416px;">
                    <a href="../external.html?link=https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15959.065741666545!2d32.5143163!3d0.2922013!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xc9089a574d73ada0!2sICT for persons with disabilites%7C%20Islamic%20University%20in%20Uganda%20-%20Females&#39;%20Campus!5e0!3m2!1sen!2sug!4v1672652271533!5m2!1sen!2sug"
                        data-iframe="true" class="gallery-item rounded-2"
                        data-sub-html='<h6 class="fs-sm text-light">Kaboja, Kabojja, Nateete, Kampala</h6>'>
                        <img src="assets/img/landing/conference/map-light.jpg" class="d-dark-mode-none"
                            alt="Map preview">
                        <img src="assets/img/landing/conference/map-dark.jpg" class="d-none d-dark-mode-block"
                            alt="Map preview">
                        <div class="gallery-item-caption fs-sm fw-medium">ICT for persons with disabilites's campus - Kaboja, Kabojja, Nateete, Kampala</div>
                    </a>
                </div>
            </div>
        </div>
    </section>


    <!-- Buy ticket CTA -->
    <section class="bg-gradient-primary py-5">
        <div class="container py-2 py-md-4 py-lg-5">
            <div class="row py-xl-3">
                <div class="col-xl-4 col-lg-5 offset-xl-1 order-lg-2 mb-4">
                    <h2 class="h1 text-light text-center text-sm-start pb-4 mb-0 mb-lg-3">When was the last time you dinned
                        with your classmates?</h2>
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle text-primary flex-shrink-0 p-3">
                            <svg style="margin: 2px;" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="../external.html?link=http://www.w3.org/2000/svg">
                                <path
                                    d="M18.0225 2.91133C17.2397 2.91133 16.5038 3.21602 15.9507 3.76914C15.3132 4.40664 14.9616 5.25508 14.9616 6.15977V8.98633C14.9616 12.516 13.0772 15.6098 10.26 17.3207H14.2585C18.0225 17.3207 21.0882 14.2598 21.0882 10.491V5.97227C21.0882 4.28945 19.71 2.91133 18.0225 2.91133ZM18.7772 5.97695C18.3882 5.97695 18.0741 5.66289 18.0741 5.27383C18.0741 4.88477 18.3882 4.5707 18.7772 4.5707C19.1663 4.5707 19.4804 4.88477 19.4804 5.27383C19.4804 5.66289 19.1663 5.97695 18.7772 5.97695ZM23.7929 1.71133C23.5163 1.43477 23.071 1.43477 22.7991 1.71133L21.4257 3.08477C20.6054 2.11914 19.3819 1.50977 18.0225 1.50977C16.8647 1.50977 15.7772 1.95977 14.9569 2.78008L0.20535 17.527C0.00378752 17.7285 -0.05715 18.0285 0.0506625 18.291C0.158475 18.5535 0.416288 18.727 0.702225 18.727H5.22097H9.4491L11.8069 21.0848H11.2444C10.8553 21.0848 10.5413 21.3988 10.5413 21.7879C10.5413 22.177 10.8553 22.491 11.2444 22.491H15.7632C16.1522 22.491 16.4663 22.177 16.4663 21.7879C16.4663 21.3988 16.1522 21.0848 15.7632 21.0848H13.7944L11.4366 18.727H14.2585C18.8007 18.727 22.4944 15.0332 22.4944 10.491V5.97227C22.4944 5.39102 22.3819 4.8332 22.1757 4.31758L23.7929 2.70039C24.0694 2.4332 24.0694 1.98789 23.7929 1.71133ZM2.3991 17.3207L13.5554 6.16445V8.98633C13.5554 13.5801 9.81472 17.3207 5.22097 17.3207H2.3991ZM21.0882 10.4957C21.0882 14.2598 18.0272 17.3254 14.2585 17.3254H10.26C13.0772 15.6145 14.9616 12.516 14.9616 8.98633V6.15977C14.9616 5.25508 15.3132 4.40664 15.9507 3.76914C16.5038 3.21602 17.2397 2.91133 18.0225 2.91133C19.71 2.91133 21.0882 4.28477 21.0882 5.97695V10.4957Z"
                                    fill="currentColor" />
                            </svg>
                        </div>
                        <p class="fs-xl text-light ps-3 mb-0">Let’s get to network and share opportunities over a meal by
                            reserving your seat at the dinner table!</p>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-7 order-lg-1">

                    <!-- Ticket card -->
                    <div class="position-relative">
                        <div class="position-relative overflow-hidden rounded-3 zindex-5 py-5 px-4 p-sm-5">
                            <span
                                class="position-absolute top-0 start-0 w-100 h-100 bg-repeat-0 bg-position-center-start zindex-2"
                                style="background-image: url(assets/img/landing/conference/price-card-left-bg.png);"></span>
                            <span
                                class="position-absolute top-0 end-0 w-100 h-100 bg-repeat-0 bg-position-center-end zindex-2"
                                style="background-image: url(assets/img/landing/conference/price-card-right-bg.png);"></span>
                            <div class="px-md-4 position-relative zindex-5">
                                <div class="d-sm-flex align-items-start justify-content-between">
                                    <div class="text-center text-sm-start me-sm-4">
                                        <div class="lead text-primary fw-semibold text-uppercase mb-2">10<sup
                                                class="text-lowercase">th</sup> FEB, 2023</div>
                                        <h3 class="h1 text-heading">ICT for persons with disabilites Grand Dinner</h3>
                                    </div>
                                    <div class="d-table bg-white rounded-3 p-4 flex-shrink-0 mx-auto mx-sm-0">
                                        <img src="assets/img/landing/conference/qr.png" width="102" alt="QR Code">
                                    </div>
                                </div>
                                <div class="d-flex flex-column flex-sm-row align-items-center pt-4 mt-2">
                                    <a href="{{ admin_url('event-bookings/create?event=1') }}"
                                        class="btn btn-primary shadow-primary btn-lg mb-3 mb-sm-0 me-sm-3">BOOK A SEAT</a>
                                    <div class="d-flex align-items-center">
                                        <span class="fs-lg me-2">from </span>
                                        <span class="h4 text-body mb-0">100K</span>
                                        <span class="fs-lg mx-2"> to </span>
                                        <span class="h4 text-body mb-0">2M</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="position-absolute bg-dark opacity-35 bottom-0 mb-n2 d-dark-mode-none"
                            style="left: 1.5rem; width: calc(100% - 3rem); height: 5rem; filter: blur(.75rem);"></span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>ICT for persons with disabilites</title>
    <base href="{{ url('') }}/">


    <!-- SEO Meta Tags -->
    <meta name="description" content="ICT for persons with disabilites">
    <meta name="keywords" content="ICT for persons with disabilites">
    <meta name="author" content="Muhindo Mubaraka - Mubahood">

    <!-- Viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Favicon and Touch Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ url('') }}/assets/img/logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url('') }}/assets/img/logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ url('') }}/assets/img/logo.png">
    <link rel="manifest" href="{{ url('') }}/assets/favicon/site.webmanifest">
    <link rel="mask-icon" href="{{ url('') }}/assets/img/logo.png" color="#6366f1">
    <link rel="shortcut icon" href="{{ url('') }}/assets/img/logo.png">
    <meta name="msapplication-TileColor" content="#080032">
    <meta name="msapplication-config" content="{{ url('') }}/assets/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

    <!-- Vendor Styles -->
    <link rel="stylesheet" media="screen" href="{{ url('') }}/assets/vendor/boxicons/css/boxicons.min.css" />
    <link rel="stylesheet" media="screen" href="{{ url('') }}/assets/vendor/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" media="screen"
        href="{{ url('') }}/assets/vendor/lightgallery/css/lightgallery-bundle.min.css" />

    <!-- Main Theme Styles + Bootstrap -->
    <link rel="stylesheet" media="screen" href="{{ url('') }}/assets/css/theme.min.css">

    <!-- Page loading styles -->
    <style>
        .page-loading {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            -webkit-transition: all .4s .2s ease-in-out;
            transition: all .4s .2s ease-in-out;
            background-color: #fff;
            opacity: 0;
            visibility: hidden;
            z-index: 9999;
        }

        .dark-mode .page-loading {
            background-color: #0b0f19;
        }

        .page-loading.active {
            opacity: 1;
            visibility: visible;
        }

        .page-loading-inner {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            text-align: center;
            -webkit-transform: translateY(-50%);
            transform: translateY(-50%);
            -webkit-transition: opacity .2s ease-in-out;
            transition: opacity .2s ease-in-out;
            opacity: 0;
        }

        .page-loading.active>.page-loading-inner {
            opacity: 1;
        }

        .page-loading-inner>span {
            display: block;
            font-size: 1rem;
            font-weight: normal;
            color: #9397ad;
        }

        .dark-mode .page-loading-inner>span {
            color: #fff;
            opacity: .6;
        }

        .page-spinner {
            display: inline-block;
            width: 2.75rem;
            height: 2.75rem;
            margin-bottom: .75rem;
            vertical-align: text-bottom;
            border: .15em solid #b4b7c9;
            border-right-color: transparent;
            border-radius: 50%;
            -webkit-animation: spinner .75s linear infinite;
            animation: spinner .75s linear infinite;
        }

        .dark-mode .page-spinner {
            border-color: rgba(255, 255, 255, .4);
            border-right-color: transparent;
        }

        @-webkit-keyframes spinner {
            100% {
                -webkit-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        @keyframes spinner {
            100% {
                -webkit-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }
    </style>

    <!-- Theme mode -->
    <script>
        let mode = window.localStorage.getItem('mode'),
            root = document.getElementsByTagName('html')[0];
        if (mode !== null && mode === 'dark') {
            root.classList.add('dark-mode');
        } else {
            root.classList.remove('dark-mode');
        }
    </script>

    <!-- Page loading scripts -->
    <script>
        (function() {
            window.onload = function() {
                const preloader = document.querySelector('.page-loading');
                preloader.classList.remove('active');
                setTimeout(function() {
                    preloader.remove();
                }, 1000);
            };
        })();
    </script>

</head>


<body>

    <div class="page-loading active">
        <div class="page-loading-inner">
            <div class="page-spinner"></div><span>Loading...</span>
        </div>
    </div>

    <div class="bg-light">


    @yield('base-content')


</div>
    @yield('footer')
    @yield('footer-2')



    <!-- Vendor Scripts -->
    <script src="{{ url('') }}/assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ url('') }}/assets/vendor/smooth-scroll/dist/smooth-scroll.polyfills.min.js"></script>
    <script src="{{ url('') }}/assets/vendor/parallax-js/dist/parallax.min.js"></script>
    <script src="{{ url('') }}/assets/vendor/rellax/rellax.min.js"></script>
    <script src="{{ url('') }}/assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="{{ url('') }}/assets/vendor/lightgallery/lightgallery.min.js"></script>
    <script src="{{ url('') }}/assets/vendor/lightgallery/plugins/video/lg-video.min.js"></script>
    <script src="{{ url('') }}/assets/vendor/lightgallery/plugins/zoom/lg-zoom.min.js"></script>
    <script src="{{ url('') }}/assets/vendor/lightgallery/plugins/fullscreen/lg-fullscreen.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Main Theme Script -->
    <script src="{{ url('') }}/assets/js/theme.min.js"></script>

    @yield('bellow-footer')


</body>

</html>


</html>

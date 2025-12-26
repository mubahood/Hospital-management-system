<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">

    <base href="{{ url('around') }}/">
    <!-- Viewport -->
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">

    <!-- SEO meta tags -->
    <title>{{ env('APP_NAME', 'GlobalHealth') }} - HR Management System</title>
    <meta name="description" content="Humana Resouse Management System - {{ env('APP_NAME', 'GlobalHealth') }} (c) 2024">
    <meta name="keywords" content="SAAS, HR Systme, 8tech">
    <meta name="author" content="{{ env('APP_NAME', 'GlobalHealth') }} (c) 2024">

    <!-- Webmanifest + Favicon / App icons -->
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="{{ url('assets/img/logo.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ url('assets/img/logo.png') }}"">

    <!-- Theme switcher (color modes) -->
    <script src="{{ url('around') }}/assets/js/theme-switcher.js"></script>

    <!-- Import Google font (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap"
        rel="stylesheet" id="google-font">

    <!-- Vendor styles -->
    <link rel="stylesheet" media="screen" href="{{ url('around') }}/assets/vendor/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" media="screen" href="{{ url('around') }}/assets/vendor/aos/dist/aos.css">

    <!-- Font icons -->
    <link rel="stylesheet" href="{{ url('around') }}/assets/icons/around-icons.min.css">

    <!-- Theme styles + Bootstrap -->
    <link rel="stylesheet" media="screen" href="{{ url('around') }}/assets/css/theme.min.css">
    
    <!-- Redesigned Admin CSS -->
    <link rel="stylesheet" href="{{ url('css/admin-redesign.css') }}">

    <!-- Customizer generated styles -->
    <style id="customizer-styles"></style>

    <style>
        --primary: red;
        
        /* FORCE SQUARE CORNERS EVERYWHERE */
        *,
        *::before,
        *::after {
            border-radius: 0 !important;
            -webkit-border-radius: 0 !important;
            -moz-border-radius: 0 !important;
        }
        
        /* REDUCE PADDING AND MARGINS */
        .card {
            padding: 12px !important;
            margin-bottom: 12px !important;
        }
        
        .card-body {
            padding: 12px !important;
        }
        
        .card-header {
            padding: 8px 12px !important;
            margin-bottom: 0 !important;
        }
        
        /* BUTTONS - FORCE SQUARE AND SMALL PADDING */
        .btn,
        button,
        input[type="button"],
        input[type="submit"],
        input[type="reset"],
        .button,
        a.btn,
        [class*="btn-"] {
            padding: 6px 12px !important;
            margin: 2px !important;
            border-radius: 0 !important;
            -webkit-border-radius: 0 !important;
            -moz-border-radius: 0 !important;
        }
        
        .btn-sm,
        .btn-group-sm > .btn {
            padding: 4px 10px !important;
        }
        
        .btn-lg,
        .btn-group-lg > .btn {
            padding: 8px 16px !important;
        }
        
        .form-control,
        .form-select,
        input,
        textarea,
        select {
            padding: 6px 10px !important;
            margin-bottom: 8px !important;
        }
        
        .table td,
        .table th {
            padding: 8px !important;
        }
        
        .modal-content {
            padding: 12px !important;
        }
        
        .modal-header,
        .modal-body,
        .modal-footer {
            padding: 10px !important;
        }
        
        .alert {
            padding: 10px 12px !important;
            margin-bottom: 10px !important;
        }
        
        .badge {
            padding: 3px 8px !important;
        }
        
        /* Navigation spacing */
        .nav-link {
            padding: 6px 12px !important;
        }
        
        /* Sidebar spacing */
        .sidebar-nav .nav-item {
            margin-bottom: 4px !important;
        }
        
        /* Container spacing */
        .container,
        .container-fluid {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
        
        /* Row and column spacing */
        .row {
            margin-left: -8px !important;
            margin-right: -8px !important;
        }
        
        [class*="col-"] {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }
        
        /* List group spacing */
        .list-group-item {
            padding: 8px 12px !important;
        }
        
        /* Dropdown spacing */
        .dropdown-menu {
            padding: 4px !important;
        }
        
        .dropdown-item {
            padding: 6px 12px !important;
        }
        
        /* Avatar and images - keep square */
        .avatar,
        .avatar img,
        img[class*="avatar"] {
            border-radius: 0 !important;
        }
        
        /* Progress bars */
        .progress {
            margin-bottom: 8px !important;
        }
        
        /* Pagination */
        .pagination {
            margin-bottom: 8px !important;
        }
        
        .page-link {
            padding: 4px 10px !important;
        }
        
        /* Tabs */
        .nav-tabs .nav-link {
            padding: 6px 12px !important;
        }
        
        /* Breadcrumbs */
        .breadcrumb {
            padding: 6px 12px !important;
            margin-bottom: 8px !important;
        }
        
        /* ADDITIONAL BUTTON SPECIFICITY - Override Around Theme */
        .btn-primary,
        .btn-secondary,
        .btn-success,
        .btn-danger,
        .btn-warning,
        .btn-info,
        .btn-light,
        .btn-dark,
        .btn-outline-primary,
        .btn-outline-secondary,
        .btn-outline-success,
        .btn-outline-danger,
        .btn-outline-warning,
        .btn-outline-info,
        .btn-outline-light,
        .btn-outline-dark,
        .btn-link {
            border-radius: 0 !important;
            -webkit-border-radius: 0 !important;
            -moz-border-radius: 0 !important;
            padding: 6px 12px !important;
        }
        
        /* Input groups and button groups */
        .input-group,
        .btn-group,
        .btn-group-vertical {
            border-radius: 0 !important;
        }
        
        .input-group > *,
        .btn-group > *,
        .btn-group-vertical > * {
            border-radius: 0 !important;
        }
        
        /* Search and filter buttons */
        .input-group-text,
        .input-group .btn,
        .input-group button {
            border-radius: 0 !important;
            padding: 6px 12px !important;
        }
        
        /* Specific overrides for common button patterns */
        button[class*="btn"],
        a[class*="btn"],
        input[class*="btn"] {
            border-radius: 0 !important;
        }
    </style>

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

        [data-bs-theme="dark"] .page-loading {
            background-color: #121519;
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
            font-family: "Inter", sans-serif;
            font-size: 1rem;
            font-weight: normal;
            color: #6f788b;
        }

        [data-bs-theme="dark"] .page-loading-inner>span {
            color: #fff;
            opacity: .6;
        }

        .page-spinner {
            display: inline-block;
            width: 2.75rem;
            height: 2.75rem;
            margin-bottom: .75rem;
            vertical-align: text-bottom;
            background-color: #d7dde2;
            border-radius: 50%;
            opacity: 0;
            -webkit-animation: spinner .75s linear infinite;
            animation: spinner .75s linear infinite;
        }

        [data-bs-theme="dark"] .page-spinner {
            background-color: rgba(255, 255, 255, .25);
        }

        @-webkit-keyframes spinner {
            0% {
                -webkit-transform: scale(0);
                transform: scale(0);
            }

            50% {
                opacity: 1;
                -webkit-transform: none;
                transform: none;
            }
        }

        @keyframes spinner {
            0% {
                -webkit-transform: scale(0);
                transform: scale(0);
            }

            50% {
                opacity: 1;
                -webkit-transform: none;
                transform: none;
            }
        }
    </style>

    <!-- Page loading scripts -->
    <script>
        (function() {
            window.onload = function() {
                const preloader = document.querySelector('.page-loading')
                preloader.classList.remove('active')
                setTimeout(function() {
                    preloader.remove()
                }, 1500)
            }
        })()
    </script>

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-WKV3GT5');
    </script>
</head>


<!-- Body -->

<body>

    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="//www.googletagmanager.com/ns.html?id=GTM-WKV3GT5" height="0" width="0"
            style="display: none; visibility: hidden;" title="Google Tag Manager"></iframe>
    </noscript>


    <!-- Page loading spinner -->
    <div class="page-loading active">
        <div class="page-loading-inner">
            <div class="page-spinner"></div>
            <span>Loading...</span>
        </div>
    </div>


    @yield('base-content')


    <!-- Vendor scripts: JS libraries and plugins -->
    <script src="{{ url('around') }}/assets/vendor/parallax-js/dist/parallax.min.js"></script>
    <script src="{{ url('around') }}/assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="{{ url('around') }}/assets/vendor/aos/dist/aos.js"></script>

    <!-- Bootstrap + Theme scripts -->
    <script src="{{ url('around') }}/assets/js/theme.min.js"></script>

    <!-- Customizer -->
    <script src="{{ url('around') }}/assets/js/customizer.min.js"></script>
</body>

</html>

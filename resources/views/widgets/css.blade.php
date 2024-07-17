<?php
use App\Models\Utils;
$ent = Utils::ent();
?><style>
    body,
    a,
    p,
    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        font-family: sans-serif!important;
         background-color: red;

    }

    .sidebar {
        background-color: #FFFFFF;
    }

    .content-header {
        background-color: #F9F9F9;
    }

    .sidebar-menu .active {
        border-left: solid 5px {{ $ent->color }} !important;
        color: {{ $ent->color }} !important;
    }


    .navbar,
    .logo,
    .sidebar-toggle,
    .user-header,
    .btn-dropbox,
    .btn-twitter,
    .btn-instagram,
    .btn-primary,
    .navbar-static-top {
        background-color: {{ $ent->color }} !important;
    }

    .dropdown-menu {
        border: none !important;
    }

    .box-success {
        border-top: {{ $ent->color }} .5rem solid !important;
    }

    :root {
        --primary: {{ $ent->color }};
    }

    .fs-6 {
        font-size: 0.8rem !important;
    }

    .fs-8 {
        font-size: 0.9rem !important;
    }

    .fs-10 {
        font-size: 1rem !important;
    }

    .fs-12 {
        font-size: 1.1rem !important;
    }

    .fs-14 {
        font-size: 1.2rem !important;
    }

    .fs-16 {
        font-size: 1.3rem !important;
    }

    .fs-18 {
        font-size: 1.4rem !important;
    }

    .fs-20 {
        font-size: 1.5rem !important;
    }

    .fs-22 {
        font-size: 1.6rem !important;
    }

    .fs-24 {
        font-size: 1.7rem !important;
    }

    .fs-26 {
        font-size: 1.8rem !important;
    }

    .fs-28 {
        font-size: 1.9rem !important;
    }

    .fs-30 {
        font-size: 2rem !important;
    }

    .fs-32 {
        font-size: 2.1rem !important;
    }

    .fs-34 {
        font-size: 2.2rem !important;
    }

    .fs-36 {
        font-size: 2.3rem !important;
    }

    .fs-38 {
        font-size: 2.4rem !important;
    }

    .fs-40 {
        font-size: 2.5rem !important;
    }

    .fs-42 {
        font-size: 2.6rem !important;
    }

    .fs-44 {
        font-size: 2.7rem !important;
    }

    .fs-46 {
        font-size: 2.8rem !important;
    }

    .fs-48 {
        font-size: 2.9rem !important;
    }

    .fs-50 {
        font-size: 3rem !important;
    }

    .fs-52 {
        font-size: 3.1rem !important;
    }

    .fs-54 {
        font-size: 3.2rem !important;
    }

    .fs-56 {
        font-size: 3.3rem !important;
    }

    .fs-58 {
        font-size: 3.4rem !important;
    }

    .fs-60 {
        font-size: 3.5rem !important;
    }

    .fw-100 {
        font-weight: 100 !important;
    }

    .fw-200 {
        font-weight: 200 !important;
    }

    .fw-300 {
        font-weight: 300 !important;
    }

    .fw-400 {
        font-weight: 400 !important;
    }

    .fw-500 {
        font-weight: 500 !important;
    }

    .fw-600 {
        font-weight: 600 !important;
    }

    .fw-700 {
        font-weight: 700 !important;
    }

    .fw-800 {
        font-weight: 800 !important;
    }

    .fw-900 {
        font-weight: 900 !important;
    }

    .fw-1000 {
        font-weight: 1000 !important;
    }

    .bg-primary {
        background-color: {{ $ent->color }} !important;
    }

    .text-primary {
        color: {{ $ent->color }} !important;
    }

    .border-primary {
        border-color: {{ $ent->color }} !important;
    }
</style>

@foreach ($css as $c)
    <link rel="stylesheet" href="{{ admin_asset("$c") }}">
@endforeach

<?php

$primt_color = '#0761BB';
?><style>
    .content-header {
        background-color: #056098;
    }

    .sidebar-menu .active {
        border-left: solid 5px {{ $primt_color }} !important;
        ;
        color: {{ $primt_color }} !important;
        ;
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
        background-color: {{ $primt_color }} !important;
    }

    .dropdown-menu {
        border: none !important;
    }

    .box-success {
        border-top: {{ $primt_color }} .5rem solid !important;
    }

    :root {
        --primary: {{ $primt_color }};
    }

    .card {
        box-shadow: rgba(0, 0, 0, 0.1) 0px 20px 25px -5px, rgba(0, 0, 0, 0.04) 0px 10px 10px -5px;
    }
</style>

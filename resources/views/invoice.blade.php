<?php

$logo = public_path('assets/img/logo-1.jpg');


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $item->id . '. Invoice #' . $item->consultation_number }}</title>

    @include('css')
    <style>
        /* margin */
        body {
            margin: 0 !important;
            padding: 0 !important;
            margin-top: 15 !important;
        }
    </style>
    <style>
        .label {
            font-size: 10px;
            font-weight: bold;
            color: #6c757d;
            line-height: 12px;
            padding: 0;
            margin: 0;
            padding-bottom: 2px;
        }

        .value {
            font-size: 14px;
            font-weight: bold;
            color: black;
            line-height: 13px;
            padding: 0;
            margin: 0;
            padding-bottom: 5px;
        }
        td{
            vertical-align: top;
        }
    </style>

</head>

<body>
    <table class="w-100">
        <tr>
            <td class="w-75">
                <h2 class="fs-40">INVOICE</h2>
            </td>
            <td style="text-align: right;">
                <img src="{{ $logo }}" alt="logo" width="120">
            </td>
        </tr>
    </table>
</body>

</html>

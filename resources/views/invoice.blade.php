<?php

//include Utils model
use App\Models\Utils;

$logo = public_path('storage/' . $company->logo);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $item->id . '. Invoice ' . $item->consultation_number }}</title>

    @include('css', [
        'company' => $company,
    ])
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

        td {
            vertical-align: top;
        }

        .my-table thead tr td {
            border-left: 1px solid #dee2e6;
        }

        .my-table {
            border: 1px solid {{ $company->color }} ! border-radius: 5px;
            /* table bordered */

            border-collapse: collapse;

            /* table width */

            width: 100%;

            /* table margin */

            margin: 0 auto;

            /* table padding */

        }

        /* make my-table striped */
        .my-table tbody tr:nth-of-type(odd) {
            background-color: #f9f9f9;

            /* table striped background color */

            border-top: 1px solid #dee2e6;

            /* table striped border color */

            border-bottom: 1px solid #dee2e6;

            /* table striped border color */

            border-left: 1px solid #dee2e6;

            /* table striped border color */

            border-right: 1px solid #dee2e6;

            /* table striped border color */

            border-radius: 5px;

            /* table striped border radius */

            /* table striped border radius */
            font-size: 12px !important;
        }

        .my-table tbody tr td {
            border-left: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
            font-size: 12px !important;

            /* table striped border color */

            border-bottom: 1px solid #dee2e6;

            /* table striped border color */

            border-top: 1px solid #dee2e6;

            /* table striped border color */

            border-radius: 5px;

            /* table striped border radius */

            /* table striped border radius */

        }
    </style>

</head>

<body>
    <table class="w-100">
        <tr>
            <td class="w-50">
                <h2 class="fs-36 mb-2">INVOICE</h2>
                <p style="font-size: 14px;">{{ $company->name }},</p>
                <p style="font-size: 14px;">{{ $company->p_o_box }},</p>
                <p style="font-size: 14px;">{{ $company->email }},</p>
                <p style="font-size: 14px;">{{ $company->phone_number }},{{ $company->phone_number_2 }}.</p>
            </td>
            <td class="w-50" style="text-align: right;">
                <img src="{{ $logo }}" alt="logo" width="120">
            </td>
        </tr>
    </table>
    <hr style="border-width: 3px; color: {{ $company->color }}; border-color: {{ $company->color }};" class="mb-2 mt-4">
    <table class="w-100">
        <tr>
            <td class="w-50">
                <p class="fs-14">INVOICE No.
                    <span class="fw-600 text-danger">{{ $item->consultation_number }}</span>
                </p>
            </td>
            <td class="w-50" style="text-align: right;">
                <p class="fs-14 text-right">PAYMENT STATUS: <span
                        class="fw-900  {{ $item->payemnt_status == 'Paid' ? 'text-success' : 'text-danger' }} ">{{ $item->payemnt_status }}</span>
                </p>
            </td>
        </tr>
    </table>
    <hr style="border-width: 3px; color: {{ $company->color }}; border-color: {{ $company->color }};"
        class="mb-4 mt-2">

    <table class="w-100">
        <tr>
            <td class="w-50">
                <p class="fs-18 fw-900 text-primary">
                    BILL TO
                </p>
                <p>{{ $item->patient_name }},</p>
                <p>{{ $item->patient->current_address }},</p>
                <p>{{ $item->patient->phone_number_1 }}{{ $item->patient->phone_number_1 != null && strlen($item->patient->phone_number_2) > 0 ? ', ' . $item->patient->phone_number_2 : '' }}{{ $item->patient->company_id != 1 ? ', ' : '.' }}
                </p>
                @if ($item->patient->company_id != 1)
                    <p>{{ $item->patient->company->name }}.</p>
                @endif
            </td>
            <td class="w-50" style="text-align: right;">
                <br>
                <p class="fs-14 text-right">DATE: <span class="fw-900">{{ Utils::my_date_2($item->created_at) }}
                    </span>
                </p>
                <p class="fs-14 text-right">UPDATED: <span class="fw-900">{{ Utils::my_date_2($item->updated_at) }}
                    </span>
                </p>
            </td>
        </tr>
    </table>

    <table class="w-100 mt-4 my-table ">
        <thead class="bg-primary text-white text-uppercase">
            <tr>
                <td style="width: 7% " class="pb-2 pt-1 pl-2">Sn.</td>
                <td class="pb-2 pt-1 pl-1" style="width: 18%">Service</td>
                <td class="pb-2 pt-1 pl-1" style="width: 55%">Description</td>
                <td class="pb-2 pt-1 pl-1 text-right pr-2">Amount</td>
            </tr>
        </thead>
        <tbody>
            <?php $sn = 1; ?>
            @foreach ($item->medical_services as $service)
                <tr>
                    <td class="pt-1 pl-2">{{ $sn++ }}</td>
                    <td class="pt-1 pl-1">{{ $service->type }}</td>
                    <td class="pt-1 pl-1">{!! $service->remarks !!}</td>
                    <td class="pt-1 pl-1 text-right pr-2">UGX {{ number_format($service->total_price, 2) }}</td>
                </tr>
            @endforeach
            @foreach ($item->billing_items as $_item)
                @php
                    if ($_item->type == 'Discount') {
                        continue;
                    }
                @endphp
                <tr style="background-color: white; border: none;">
                    <td colspan="2" style="border: none!important; background-color: none!important;"></td>
                    <td class="pt-1
                        pl-1">({{ $_item->type }}) {{ $_item->description }}</td>
                    <td class="pt-1 pl-1 text-right pr-2">UGX {{ number_format($_item->price, 2) }}</td>
                </tr>
            @endforeach
            <tr style="background-color: white; border: none;">
                <td colspan="2" style="border: none!important; background-color: none!important;"></td>
                <td class="pt-1
                    pl-1 text-uppercase"><b>Subtotal</b></td>
                <td class="pt-1 pl-1 text-right pr-2"><b>UGX {{ number_format($item->subtotal, 2) }}</b></td>
            </tr>
            @foreach ($item->billing_items as $_item)
                @php
                    if ($_item->type != 'Discount') {
                        continue;
                    }
                @endphp
                <tr style="background-color: white; border: none;">
                    <td colspan="2" style="border: none!important; background-color: none!important;"></td>
                    <td class="pt-1
                        pl-1">({{ $_item->type }}) {{ $_item->description }}</td>
                    <td class="pt-1 pl-1 text-right pr-2">(UGX {{ number_format($_item->price, 2) }})</td>
                </tr>
            @endforeach

            <tr style="background-color: white; border: none;">
                <td colspan="2" style="border: none!important; background-color: none!important;"></td>
                <td class="pt-1
                    pl-1 text-uppercase"><b>Total</b></td>
                <td class="pt-1 pl-1 text-right pr-2"><b>UGX {{ number_format($item->total_charges, 2) }}</b></td>
            </tr>
        </tbody>
    </table>

    <div class="mt-4 p-2" style="border: 2px solid {{ $company->color }}; ">
        <b class="text-uppercase">Notes:</b>
        <p>Total Paid: UGX {{ number_format($item->total_paid, 0) }}, Total Due: UGX
            {{ number_format($item->total_due, 0) }}.</p>
    </div>
    {{-- 
      "" => 82843
    "" => 1000
    "total_due" => 81843
    --}}
    <p class="text-center mt-1"><b>Thank you.</b></p>
</body>

</html>

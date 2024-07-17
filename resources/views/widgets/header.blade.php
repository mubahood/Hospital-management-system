<table class="w-100 ">
    <tbody>
        <tr>
            <td style="width: 13%;" class="">
                <img class="img-fluid" style="width: 90%" src="{{ $logo_link }}" alt="{{ $company->name }}">
            </td>
            <td class=" text-center">
                <p class="fw-600 fs-18  text-uppercase">{{ $company->name }}</p>
                <p class="mt-2">Address: {{ $company->address }}, {{ $company->p_o_box }}</p>
                <p class="mt-0">Website: {{ $company->website }}, Email: {{ $company->email }}</p>
                <p class="mt-0">Tel: <b>{{ $company->phone_number }}</b> , <b>{{ $company->phone_number_2 }}</b>
                </p>
            </td>
            <td style="width: 10%;"><br></td>
        </tr>
    </tbody>
</table>
<hr style="border-width: 4px; color: {{ $company->color }}; border-color: {{ $company->color }};" class="mt-3 mb-1">
<hr style="border-width: 3px; color: black; border-color: black;" class="mb-1 mt-0">

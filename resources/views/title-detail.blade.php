@php
    $t = $t ?? '';
    $d = $d ?? '';
    $style = $style ?? '1';
@endphp
@if ($style == '1')
    <p class="fs-16 my-1"><span class="">{{ $t }}: </span><span class="fw-700">{{ $d }}</span>
    </p>
@elseif ($style == '2')
    <p class="fs-14 mt-1 mb-0" style="color: grey"><span class="">{{ $t }}</p>
    <p class="fs-16 mb-2 mt-0"></span><span class="fw-700">{{ $d }}</span></p>
@endif

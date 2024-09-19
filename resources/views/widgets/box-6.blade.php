<?php

$title = isset($title) ? $title : 'Title';
$style = isset($style) ? $style : 'success';
$number = isset($number) ? $number : '0.00';
$sub_title = isset($sub_title) ? $sub_title : 'Sub-titles';
$link = isset($link) ? $link : 'javascript:;';

if (!isset($is_dark)) {
    $is_dark = true;
}
if (!isset($icon)) {
    $icon = 'box';
}
$is_dark = ((bool) $is_dark);

$bg = '';
$text = 'text-dark';
$border = 'border-primary';
$text2 = 'text-dark';
if ($is_dark) {
    $bg = 'bg-primary';
    $text = 'text-white';
    $text2 = 'text-white';
}

if ($style == 'danger') {
    $text = 'text-white';
    $bg = 'bg-danger';
    $text2 = 'text-white';
    $border = 'border-danger';
}
?><a href="{{ $link }}" class="card {{ $bg }} mb-4 mb-md-5">
    <div class="card-body py-10 text-capitalize">
        <div class="bg-primary d-inline-block px-3 mb-4 py-1" style="border-radius: 1.5rem;">
            <i class="bi bi-{{ $icon }} fs-40"></i>
        </div>
        <div style="height: 4.5rem;" class="">
            <p class="fs-18 fw-400 mt-2 mb-2 {!! $text !!} ">{!! $title !!}</p>
        </div>
        <p class="m-0 text-right {{ $text2 }} fw-800 fs-34">{{ $number }}</p>
    </div>
</a>

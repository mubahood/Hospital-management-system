<?php

$name = isset($name) ? $name : '';
$type = isset($type) ? $type : 'text';
$classes = isset($classes) ? $classes : '';
$attributes = isset($attributes) ? $attributes : [];

$_attributes = '';
foreach ($attributes as $key => $value) {
    $_attributes .= ' ' . ($key = '"' . $value . '" ');
}

$is_password = false;
if ($type == 'password') {
    $is_password = true;
}

$value = old($name);
if (isset($_SESSION['form'])) {
    if (isset($_SESSION['form']->$name)) {
        if ($_SESSION['form']->$name != null) {
            $value = $_SESSION['form']->$name;
        }
    }
}

?><div class="form-group ">
    <label for="{{ $name ?? '' }}" class="form-label fs-base">{!! $label ?? '' !!}</label>

    @if ($is_password)
        <div class="password-toggle">
    @endif

    @if ($type == 'textarea')
        <textarea {{ $_attributes ?? '' }} id="{{ $name ?? '' }}" class="form-control " rows="4" name="{{ $name }}">{{ $value }}</textarea>
    @else
        <input name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" id="{{ $name ?? '' }}"
            class="form-control  {{ $classes }}" {{ $_attributes ?? '' }}>
    @endif




    @if ($is_password)
        <label class="password-toggle-btn" aria-label="Show/hide password">
            <input class="password-toggle-check" type="checkbox">
            <span class="password-toggle-indicator"></span>
        </label>
    @endif
    @if ($is_password)
</div>
@endif

@if ($errors->has($name))
    @foreach ($errors->get($name) as $message)
        <div class="text-danger top-100 small">{{ $message }}</div>
    @endforeach
@endif

</div>

<?php
$tagId = rand(100000000, 999999999);

$_hideActions = isset($hideActions) ? $hideActions : false;
$_hideView = isset($hideView) ? $hideView : false;
$_hideEdit = isset($hideEdit) ? $hideEdit : false;
$_links = isset($links) ? $links : [];
?><div class="dropdown dropleft">
    <a class="dropdown-toggle" href="#" id="dropdownMenuButton{{ $tagId }}" data-toggle="dropdown"
        aria-haspopup="false" aria-expanded="false" style="font-size: 1.2em;">
        &nbsp;
        <i class="fa fa-ellipsis-v"></i>
        &nbsp;
    </a>
    <div class="dropdown-menu" 
        style="
            /* add border shaddow */
            box-shadow: 0 0 10px rgba(0,0,0,0.1)!important;
            /* add border radius */
            border-radius: 0.5rem!important;
            /* add padding */
            padding: 0rem!important;
            /* add margin */
            margin: 0!important;
            /* add border */
            border: 1px solid rgba(0,0,0,0.1)!important;
            /* add background color */
            background-color: #fff!important;
            /* add z-index */
            z-index: 9999!important;
            /* add position */
            position: sticky!important;
            /* add top */
            top: 0!important;
            /* add left */

        "
    aria-labelledby="dropdownMenuButton{{ $tagId }}">
        @if (!$_hideActions)
            @if (!$_hideView)
                <a class="dropdown-item" href="{{ admin_url('endpoint/' . $id) }}">
                    <i class="fa fa-eye text-primary"></i> View
                </a>
            @endif
            @if (!$_hideEdit)
                <a class="dropdown-item" href="{{ admin_url('endpoint/' . $id . '/edit') }}">
                    <i class="fa fa-edit text-primary"></i> Edit
                </a>
            @endif
        @endif
        @foreach ($_links as $link)
            <a class="dropdown-item py-3"
                @isset($link['newTab'])
                    target="_blank"
                @endisset
                href="{{ $link['url'] }}">
                <i class="fa fa-{{ $link['icon'] }} text-primary"></i> {{ $link['label'] }}
            </a>
        @endforeach
    </div>
</div>

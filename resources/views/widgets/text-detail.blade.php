<?php

if ($s == null || trim($s) == '' || $s == 'null' || $s == 'NULL') {
    $s = '-';
}

?><p class="fs-14 mb-1">{{ strtoupper($t) }}: <span class="fw-900">{{ $s }}</span></p>

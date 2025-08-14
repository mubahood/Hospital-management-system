<?php
/**
 * Quick action buttons component for common hospital tasks
 * 
 * @param array $actions - Array of action configurations
 * @param string $title - Component title
 * @param string $icon - Title icon
 */

$title = $title ?? 'Quick Actions';
$icon = $icon ?? 'fas fa-bolt';
$actions = $actions ?? [];
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="{{ $icon }} me-2"></i>
            {{ $title }}
        </h5>
    </div>
    <div class="card-body p-3">
        <div class="row g-2">
            @foreach($actions as $action)
                @php
                    $btnClass = $action['class'] ?? 'btn-outline-primary';
                    $icon = $action['icon'] ?? 'fas fa-plus';
                    $url = $action['url'] ?? '#';
                    $label = $action['label'] ?? 'Action';
                    $target = $action['target'] ?? '_self';
                    $permission = $action['permission'] ?? null;
                    $tooltip = $action['tooltip'] ?? $label;
                @endphp
                
                @if(!$permission || Auth::user()->can($permission))
                    <div class="col-6 col-md-4 col-lg-3">
                        <a 
                            href="{{ $url }}" 
                            class="btn {{ $btnClass }} w-100 d-flex flex-column align-items-center p-3"
                            target="{{ $target }}"
                            data-bs-toggle="tooltip"
                            title="{{ $tooltip }}"
                        >
                            <i class="{{ $icon }} fs-4 mb-2"></i>
                            <span class="small text-center">{{ $label }}</span>
                        </a>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

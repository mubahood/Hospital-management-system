<?php
/**
 * Hospital notification system component
 * 
 * @param array $notifications - Array of notification objects
 * @param string $type - Notification type (success, warning, danger, info)
 * @param bool $dismissible - Whether notifications can be dismissed
 * @param bool $showIcon - Whether to show notification icons
 */

$notifications = $notifications ?? [];
$type = $type ?? 'info';
$dismissible = $dismissible ?? true;
$showIcon = $showIcon ?? true;

// Get icon based on type
$icons = [
    'success' => 'fas fa-check-circle',
    'warning' => 'fas fa-exclamation-triangle', 
    'danger' => 'fas fa-exclamation-circle',
    'info' => 'fas fa-info-circle'
];

$icon = $icons[$type] ?? $icons['info'];
?>

@if(count($notifications) > 0)
    <div class="notification-container mb-4">
        @foreach($notifications as $notification)
            @php
                $notificationType = $notification['type'] ?? $type;
                $notificationIcon = $icons[$notificationType] ?? $icon;
                $message = $notification['message'] ?? '';
                $title = $notification['title'] ?? '';
                $action = $notification['action'] ?? null;
                $timestamp = $notification['timestamp'] ?? now();
                $priority = $notification['priority'] ?? 'normal';
                $isImportant = $priority === 'high' || $priority === 'urgent';
            @endphp
            
            <div class="alert alert-{{ $notificationType }} {{ $dismissible ? 'alert-dismissible' : '' }} {{ $isImportant ? 'border-start border-5' : '' }} fade show" role="alert">
                <div class="d-flex align-items-start">
                    @if($showIcon)
                        <div class="flex-shrink-0 me-3">
                            <i class="{{ $notificationIcon }} fs-4"></i>
                        </div>
                    @endif
                    
                    <div class="flex-grow-1">
                        @if($title)
                            <h6 class="alert-heading mb-1">
                                {{ $title }}
                                @if($isImportant)
                                    <span class="badge bg-{{ $notificationType === 'danger' ? 'danger' : 'warning' }} ms-2">
                                        {{ strtoupper($priority) }}
                                    </span>
                                @endif
                            </h6>
                        @endif
                        
                        <p class="mb-2">{{ $message }}</p>
                        
                        @if($action)
                            <div class="mt-2">
                                <a 
                                    href="{{ $action['url'] }}" 
                                    class="btn btn-{{ $notificationType }} btn-sm"
                                    @if(isset($action['target'])) target="{{ $action['target'] }}" @endif
                                >
                                    @if(isset($action['icon']))
                                        <i class="{{ $action['icon'] }} me-1"></i>
                                    @endif
                                    {{ $action['label'] }}
                                </a>
                            </div>
                        @endif
                        
                        @if($timestamp)
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-clock me-1"></i>
                                {{ \Carbon\Carbon::parse($timestamp)->diffForHumans() }}
                            </small>
                        @endif
                    </div>
                </div>
                
                @if($dismissible)
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                @endif
            </div>
        @endforeach
    </div>
@endif

@if(session('success') || session('error') || session('warning') || session('info'))
    <div class="notification-container mb-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fs-4 me-3"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle fs-4 me-3"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fs-4 me-3"></i>
                    <div>{{ session('warning') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle fs-4 me-3"></i>
                    <div>{{ session('info') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>
@endif

@push('styles')
<style>
.notification-container .alert {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 8px;
}

.notification-container .alert.border-5 {
    border-left-width: 5px !important;
}

.notification-container .alert-heading {
    font-weight: 600;
}

.notification-container .badge {
    font-size: 0.7em;
}

.notification-container .btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}
</style>
@endpush

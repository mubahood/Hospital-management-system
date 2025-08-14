@extends('admin::index')

@section('content')
<div class="enhanced-layout">
    <!-- Page Header with Enhanced Actions -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="page-icon me-3">
                    <i class="{{ $pageIcon ?? 'fas fa-file-alt' }} fa-2x text-primary"></i>
                </div>
                <div>
                    <h1 class="page-title mb-1">{{ $pageTitle ?? 'Page Title' }}</h1>
                    <p class="page-subtitle text-muted mb-0">{{ $pageSubtitle ?? 'Manage your hospital data efficiently' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            @if(isset($quickActions) && $quickActions)
                <x-quick-actions :actions="$quickActions" />
            @endif
        </div>
    </div>

    <!-- Enhanced Search and Filters -->
    @if(isset($enableSearch) && $enableSearch)
        <div class="row mb-4">
            <div class="col-12">
                <x-enhanced-search 
                    :search-placeholder="$searchPlaceholder ?? 'Search records...'"
                    :filters="$searchFilters ?? []"
                    :sort-options="$sortOptions ?? []"
                    :export-enabled="$exportEnabled ?? true"
                    :export-formats="$exportFormats ?? ['pdf', 'excel', 'csv']"
                />
            </div>
        </div>
    @endif

    <!-- Notifications -->
    <x-notifications />

    <!-- Main Content Area -->
    <div class="row">
        @if(isset($sidebarContent))
            <div class="col-md-3">
                <div class="sidebar-content">
                    {{ $sidebarContent }}
                </div>
            </div>
            <div class="col-md-9">
        @else
            <div class="col-12">
        @endif
                <div class="main-content-area">
                    @yield('main-content')
                </div>
        @if(isset($sidebarContent))
            </div>
        @else
            </div>
        @endif
    </div>

    <!-- Enhanced Modal Template -->
    <div class="modal fade" id="enhancedModal" tabindex="-1" aria-labelledby="enhancedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="enhancedModalLabel">
                        <i class="modal-icon me-2"></i>
                        <span class="modal-title-text">Modal Title</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="modal-content-area">
                        <!-- Dynamic content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary modal-save-btn">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Processing your request...</p>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Enhanced Layout Styles */
.enhanced-layout {
    padding: 20px;
}

.page-icon i {
    opacity: 0.8;
}

.page-title {
    font-size: 2.2rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.page-subtitle {
    font-size: 1rem;
    line-height: 1.4;
}

.sidebar-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}

.main-content-area {
    background: white;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 0 15px rgba(0,0,0,0.05);
    border: 1px solid #e9ecef;
    min-height: 400px;
}

/* Enhanced Modal Styles */
.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.modal-header {
    border-radius: 12px 12px 0 0;
    padding: 20px 25px;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.modal-title {
    font-size: 1.3rem;
    font-weight: 600;
}

.modal-body {
    padding: 25px;
}

.modal-footer {
    padding: 20px 25px;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 0 0 12px 12px;
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.loading-content {
    text-align: center;
    padding: 30px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .enhanced-layout {
        padding: 15px;
    }
    
    .page-title {
        font-size: 1.8rem;
    }
    
    .main-content-area {
        padding: 20px;
    }
    
    .modal-dialog {
        margin: 10px;
    }
    
    .modal-body,
    .modal-footer {
        padding: 20px;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 1.6rem;
    }
    
    .main-content-area {
        padding: 15px;
    }
    
    .modal-lg {
        max-width: calc(100vw - 20px);
    }
}

/* Animation Classes */
.fade-in {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.slide-in {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from { transform: translateX(-100%); }
    to { transform: translateX(0); }
}

/* Enhanced Card Styles */
.enhanced-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    margin-bottom: 20px;
}

.enhanced-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.enhanced-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 20px 25px;
    border-bottom: none;
}

.enhanced-card-body {
    padding: 25px;
}

/* Status Badges */
.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.active { background: #d4edda; color: #155724; }
.status-badge.inactive { background: #f8d7da; color: #721c24; }
.status-badge.pending { background: #fff3cd; color: #856404; }
.status-badge.completed { background: #d1ecf1; color: #0c5460; }

/* Enhanced Tables */
.enhanced-table {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.enhanced-table thead th {
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    padding: 15px;
}

.enhanced-table tbody td {
    padding: 15px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
}

.enhanced-table tbody tr:hover {
    background: #f8f9fa;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Enhanced Modal Functionality
    $('.modal-trigger').on('click', function() {
        const modalId = $(this).data('modal') || 'enhancedModal';
        const modalTitle = $(this).data('title') || 'Modal';
        const modalIcon = $(this).data('icon') || 'fas fa-edit';
        const contentUrl = $(this).data('url');
        
        const modal = $('#' + modalId);
        modal.find('.modal-title-text').text(modalTitle);
        modal.find('.modal-icon').attr('class', modalIcon + ' me-2');
        
        if (contentUrl) {
            showLoading();
            modal.find('.modal-content-area').load(contentUrl, function() {
                hideLoading();
                modal.modal('show');
            });
        } else {
            modal.modal('show');
        }
    });

    // Loading Overlay Functions
    window.showLoading = function() {
        $('#loadingOverlay').fadeIn(200);
    };

    window.hideLoading = function() {
        $('#loadingOverlay').fadeOut(200);
    };

    // Enhanced AJAX Form Submission
    $(document).on('submit', '.enhanced-form', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
        
        $.ajax({
            url: form.attr('action'),
            method: form.attr('method') || 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message || 'Operation completed successfully');
                    if (response.redirect) {
                        setTimeout(() => window.location.href = response.redirect, 1000);
                    } else if (response.reload) {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } else {
                    showNotification('error', response.message || 'An error occurred');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while processing your request';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification('error', message);
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);
            }
        });
    });

    // Enhanced Notification System
    window.showNotification = function(type, message, duration = 5000) {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        const icon = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        }[type] || 'fas fa-info-circle';

        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show notification-alert" role="alert">
                <i class="${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);

        $('.notification-container').append(notification);
        
        // Auto-dismiss after duration
        setTimeout(function() {
            notification.alert('close');
        }, duration);
    };

    // Enhanced Data Export
    $('.export-btn').on('click', function() {
        const format = $(this).data('format');
        const url = $(this).data('url');
        const filename = $(this).data('filename') || 'hospital_data';
        
        showLoading();
        
        // Create a temporary link and trigger download
        const link = document.createElement('a');
        link.href = url + '?format=' + format + '&filename=' + filename;
        link.download = filename + '.' + format;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        setTimeout(hideLoading, 1000);
    });

    // Enhanced Search Functionality
    let searchTimeout;
    $('.enhanced-search-input').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val();
        
        searchTimeout = setTimeout(function() {
            if (searchTerm.length >= 2 || searchTerm.length === 0) {
                performSearch(searchTerm);
            }
        }, 500);
    });

    function performSearch(term) {
        const searchUrl = $('.enhanced-search-form').data('search-url');
        if (!searchUrl) return;
        
        $.ajax({
            url: searchUrl,
            data: { search: term },
            success: function(response) {
                if (response.html) {
                    $('.search-results-container').html(response.html);
                }
            }
        });
    }

    // Add smooth scrolling to page anchors
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $($(this).attr('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });

    // Add animation classes to elements when they come into view
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);

    $('.enhanced-card, .main-content-area').each(function() {
        observer.observe(this);
    });
});
</script>
@endpush

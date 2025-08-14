<?php
/**
 * Enhanced search component with filters and sorting
 * 
 * @param string $placeholder - Search placeholder text
 * @param string $action - Form action URL
 * @param array $filters - Available filter options
 * @param array $sortOptions - Available sort options
 * @param string $currentSearch - Current search query
 * @param string $currentFilter - Current filter value
 * @param string $currentSort - Current sort value
 */

$placeholder = $placeholder ?? 'Search...';
$action = $action ?? request()->url();
$filters = $filters ?? [];
$sortOptions = $sortOptions ?? [];
$currentSearch = $currentSearch ?? request('search');
$currentFilter = $currentFilter ?? request('filter');
$currentSort = $currentSort ?? request('sort');
$showExport = $showExport ?? true;
$exportTypes = $exportTypes ?? ['pdf', 'excel', 'csv'];
?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ $action }}" class="row g-3 align-items-end">
            <!-- Search Input -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    <i class="fas fa-search me-1"></i>
                    Search
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="{{ $placeholder }}"
                        value="{{ $currentSearch }}"
                    >
                </div>
            </div>

            <!-- Filter Dropdown -->
            @if(count($filters) > 0)
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-filter me-1"></i>
                        Filter
                    </label>
                    <select name="filter" class="form-select">
                        <option value="">All Records</option>
                        @foreach($filters as $value => $label)
                            <option value="{{ $value }}" {{ $currentFilter == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Sort Dropdown -->
            @if(count($sortOptions) > 0)
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-sort me-1"></i>
                        Sort By
                    </label>
                    <select name="sort" class="form-select">
                        @foreach($sortOptions as $value => $label)
                            <option value="{{ $value }}" {{ $currentSort == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>
                        Search
                    </button>
                    
                    @if($currentSearch || $currentFilter || $currentSort)
                        <a href="{{ $action }}" class="btn btn-outline-secondary" title="Clear Filters">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Export Options -->
            @if($showExport)
                <div class="col-12">
                    <div class="border-top pt-3 mt-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-download me-1"></i>
                                Export Data:
                            </span>
                            <div class="btn-group" role="group">
                                @foreach($exportTypes as $type)
                                    <button 
                                        type="submit" 
                                        name="export" 
                                        value="{{ $type }}" 
                                        class="btn btn-outline-primary btn-sm"
                                        title="Export as {{ strtoupper($type) }}"
                                    >
                                        <i class="fas fa-file-{{ $type === 'excel' ? 'excel' : ($type === 'pdf' ? 'pdf' : 'csv') }} me-1"></i>
                                        {{ strtoupper($type) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>

<!-- Search Results Summary -->
@if($currentSearch || $currentFilter)
    <div class="alert alert-info mb-3">
        <i class="fas fa-info-circle me-1"></i>
        <strong>Search Results:</strong>
        @if($currentSearch)
            Searching for "{{ $currentSearch }}"
        @endif
        @if($currentFilter && isset($filters[$currentFilter]))
            @if($currentSearch) with @endif
            Filter: {{ $filters[$currentFilter] }}
        @endif
        @if(isset($totalResults))
            <span class="badge bg-primary ms-2">{{ number_format($totalResults) }} results</span>
        @endif
    </div>
@endif

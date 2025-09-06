@extends('layouts.admin')

@section('page-content')
<div>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Events</h1>
                <p class="text-gray-600 dark:text-gray-400">Manage all hospital events and activities</p>
            </div>
            <a href="{{ route('app.events.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                New Event
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-6 rounded-md p-4 {{ session('message_type') === 'error' ? 'bg-red-50 border border-red-200' : 'bg-green-50 border border-green-200' }}">
            <div class="flex">
                <div class="flex-shrink-0">
                    @if (session('message_type') === 'error')
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @else
                        <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @endif
                </div>
                <div class="ml-3">
                    <p class="text-sm {{ session('message_type') === 'error' ? 'text-red-800' : 'text-green-800' }}">
                        {{ session('message') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Filters and Search -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                    <input 
                        wire:model.debounce.300ms="search" 
                        type="text" 
                        id="search"
                        placeholder="Search events..." 
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select 
                        wire:model="statusFilter" 
                        id="status-filter"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- Date Filter -->
                <div>
                    <label for="date-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                    <select 
                        wire:model="dateFilter" 
                        id="date-filter"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="all">All Dates</option>
                        <option value="today">Today</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="past">Past</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                    </select>
                </div>

                <!-- Clear Filters -->
                <div class="flex items-end">
                    <button 
                        wire:click="clearFilters"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Events Table -->
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th wire:click="sortBy('title')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                            <div class="flex items-center space-x-1">
                                <span>Title</span>
                                @if ($sortField === 'title')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'transform rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('event_date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                            <div class="flex items-center space-x-1">
                                <span>Date & Time</span>
                                @if ($sortField === 'event_date')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'transform rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th wire:click="sortBy('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                            <div class="flex items-center space-x-1">
                                <span>Created</span>
                                @if ($sortField === 'created_at')
                                    <svg class="w-4 h-4 {{ $sortDirection === 'asc' ? 'transform rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($events as $event)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $event->title }}</div>
                                    @if($event->description)
                                        <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                            {{ Str::limit($event->description, 50) }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}
                                </div>
                                @if($event->event_time)
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $event->location ?: 'Not specified' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button 
                                    wire:click="toggleEventStatus({{ $event->id }})"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer {{ $event->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-300 dark:hover:bg-green-800' : 'bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}"
                                >
                                    {{ $event->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $event->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('app.events.show', $event->id) }}" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('app.events.edit', $event->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <button 
                                        wire:click="deleteEvent({{ $event->id }})" 
                                        onclick="return confirm('Are you sure you want to delete this event?')"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No events found</h3>
                                    <p class="text-sm">No events match your current filters.</p>
                                    @if($search || $statusFilter !== 'all' || $dateFilter !== 'all')
                                        <button 
                                            wire:click="clearFilters"
                                            class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 dark:bg-primary-900 dark:text-primary-300 dark:hover:bg-primary-800"
                                        >
                                            Clear Filters
                                        </button>
                                    @else
                                        <a href="{{ route('app.events.create') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                                            Create Your First Event
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($events->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

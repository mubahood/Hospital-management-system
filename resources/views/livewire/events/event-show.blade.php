@extends('layouts.admin')

@section('page-content')
<div>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <a href="{{ route('app.events.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                Events
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="flex-shrink-0 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="ml-4 text-sm font-medium text-gray-500 dark:text-gray-400">{{ $event->title }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $event->title }}</h1>
            </div>
            <div class="flex items-center space-x-3">
                <button 
                    wire:click="toggleEventStatus"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md {{ $event->is_active ? 'text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900 dark:text-red-300 dark:hover:bg-red-800' : 'text-green-700 bg-green-50 hover:bg-green-100 dark:bg-green-900 dark:text-green-300 dark:hover:bg-green-800' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    {{ $event->is_active ? 'Deactivate' : 'Activate' }}
                </button>
                <a href="{{ route('app.events.edit', $event->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Event
                </a>
                <button 
                    wire:click="deleteEvent" 
                    onclick="return confirm('Are you sure you want to delete this event? This action cannot be undone.')"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete Event
                </button>
            </div>
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

    <!-- Event Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Event Information</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $event->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $event->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    @if($event->description)
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Description</h4>
                            <p class="text-gray-900 dark:text-white whitespace-pre-line">{{ $event->description }}</p>
                        </div>
                    @endif

                    <!-- Date and Time -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Event Date</h4>
                            <div class="flex items-center text-gray-900 dark:text-white">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ \Carbon\Carbon::parse($event->event_date)->format('F j, Y') }}
                            </div>
                        </div>

                        @if($event->event_time)
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Event Time</h4>
                                <div class="flex items-center text-gray-900 dark:text-white">
                                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($event->event_time)->format('g:i A') }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Location -->
                    @if($event->location)
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Location</h4>
                            <div class="flex items-center text-gray-900 dark:text-white">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $event->location }}
                            </div>
                        </div>
                    @endif

                    <!-- Additional Notes -->
                    @if($event->notes)
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Internal Notes</h4>
                            <p class="text-gray-900 dark:text-white whitespace-pre-line">{{ $event->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Registration Information -->
            @if($event->max_participants || $event->registration_deadline)
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Registration Details</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            @if($event->max_participants)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Maximum Participants</h4>
                                    <div class="flex items-center text-gray-900 dark:text-white">
                                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        {{ number_format($event->max_participants) }} people
                                    </div>
                                </div>
                            @endif

                            @if($event->registration_deadline)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Registration Deadline</h4>
                                    <div class="flex items-center text-gray-900 dark:text-white">
                                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ \Carbon\Carbon::parse($event->registration_deadline)->format('F j, Y') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Contact Information -->
            @if($event->contact_email || $event->contact_phone)
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Contact Information</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        @if($event->contact_email)
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Email</h4>
                                <a href="mailto:{{ $event->contact_email }}" class="flex items-center text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $event->contact_email }}
                                </a>
                            </div>
                        @endif

                        @if($event->contact_phone)
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Phone</h4>
                                <a href="tel:{{ $event->contact_phone }}" class="flex items-center text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    {{ $event->contact_phone }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Event Meta -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Event Details</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Created</h4>
                        <p class="text-gray-900 dark:text-white">{{ $event->created_at->format('F j, Y \a\t g:i A') }}</p>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Last Updated</h4>
                        <p class="text-gray-900 dark:text-white">{{ $event->updated_at->format('F j, Y \a\t g:i A') }}</p>
                    </div>

                    @php
                        $eventDateTime = \Carbon\Carbon::parse($event->event_date . ' ' . ($event->event_time ?: '00:00:00'));
                        $now = \Carbon\Carbon::now();
                        $isUpcoming = $eventDateTime->isFuture();
                        $isPast = $eventDateTime->isPast();
                        $isToday = $eventDateTime->isToday();
                    @endphp

                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Event Status</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($isToday) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                            @elseif($isUpcoming) bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                            @elseif($isPast) bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                            @endif">
                            @if($isToday)
                                Today
                            @elseif($isUpcoming)
                                Upcoming
                            @elseif($isPast)
                                Past Event
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

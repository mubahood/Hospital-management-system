@extends('layouts.admin')

@section('page-content')
<div>
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $isEdit ? 'Edit Event' : 'Create New Event' }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ $isEdit ? 'Update the event details below' : 'Fill in the details to create a new event' }}
                </p>
            </div>
            <div class="flex space-x-3">
                <button 
                    wire:click="cancel"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    Cancel
                </button>
                @if(!$isEdit)
                    <button 
                        wire:click="resetForm"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        Reset Form
                    </button>
                @endif
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

    <!-- Form -->
    <form wire:submit.prevent="save">
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="p-6 space-y-6">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Basic Information</h3>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Event Title *
                            </label>
                            <input 
                                wire:model.defer="title" 
                                type="text" 
                                id="title"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('title') border-red-500 @enderror"
                                placeholder="Enter event title"
                            >
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Description
                            </label>
                            <textarea 
                                wire:model.defer="description" 
                                id="description"
                                rows="4"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('description') border-red-500 @enderror"
                                placeholder="Enter event description"
                            ></textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Date and Time -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Date & Time</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Event Date -->
                        <div>
                            <label for="event_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Event Date *
                            </label>
                            <input 
                                wire:model.defer="event_date" 
                                type="date" 
                                id="event_date"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('event_date') border-red-500 @enderror"
                            >
                            @error('event_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Event Time -->
                        <div>
                            <label for="event_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Event Time
                            </label>
                            <input 
                                wire:model.defer="event_time" 
                                type="time" 
                                id="event_time"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('event_time') border-red-500 @enderror"
                            >
                            @error('event_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Location and Settings -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Location & Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Location -->
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Location
                            </label>
                            <input 
                                wire:model.defer="location" 
                                type="text" 
                                id="location"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('location') border-red-500 @enderror"
                                placeholder="Enter event location"
                            >
                            @error('location')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Max Participants -->
                        <div>
                            <label for="max_participants" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Maximum Participants
                            </label>
                            <input 
                                wire:model.defer="max_participants" 
                                type="number" 
                                id="max_participants"
                                min="1"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('max_participants') border-red-500 @enderror"
                                placeholder="No limit"
                            >
                            @error('max_participants')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Registration Deadline -->
                        <div>
                            <label for="registration_deadline" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Registration Deadline
                            </label>
                            <input 
                                wire:model.defer="registration_deadline" 
                                type="date" 
                                id="registration_deadline"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('registration_deadline') border-red-500 @enderror"
                            >
                            @error('registration_deadline')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Status
                            </label>
                            <div class="flex items-center">
                                <input 
                                    wire:model.defer="is_active" 
                                    type="checkbox" 
                                    id="is_active"
                                    class="rounded border-gray-300 dark:border-gray-600 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Event is active
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Contact Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Contact Email -->
                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Contact Email
                            </label>
                            <input 
                                wire:model.defer="contact_email" 
                                type="email" 
                                id="contact_email"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('contact_email') border-red-500 @enderror"
                                placeholder="contact@example.com"
                            >
                            @error('contact_email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contact Phone -->
                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Contact Phone
                            </label>
                            <input 
                                wire:model.defer="contact_phone" 
                                type="tel" 
                                id="contact_phone"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('contact_phone') border-red-500 @enderror"
                                placeholder="(555) 123-4567"
                            >
                            @error('contact_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Additional Notes</h3>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Internal Notes
                        </label>
                        <textarea 
                            wire:model.defer="notes" 
                            id="notes"
                            rows="3"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('notes') border-red-500 @enderror"
                            placeholder="Any additional notes or instructions for internal use"
                        ></textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-end space-x-3">
                    <button 
                        type="button"
                        wire:click="cancel"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        Cancel
                    </button>
                    
                    <button 
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="save">
                            {{ $isEdit ? 'Update Event' : 'Create Event' }}
                        </span>
                        <span wire:loading wire:target="save">
                            {{ $isEdit ? 'Updating...' : 'Creating...' }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

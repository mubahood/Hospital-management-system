<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Event Management</h1>
        <button wire:click="openCreateModal" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create Event
        </button>
    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Events</label>
            <input wire:model.debounce.300ms="search" 
                   type="text" 
                   id="search"
                   placeholder="Search by title or description..." 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Filter by Status</label>
            <select wire:model="statusFilter" 
                    id="status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <!-- Events Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($events as $event)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $event->title }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($event->description, 50) }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>{{ $event->event_date }}</div>
                            <div class="text-gray-500">{{ $event->event_time }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $event->location }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($event->event_conducted === 'Completed') bg-green-100 text-green-800
                                @elseif($event->event_conducted === 'Pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ $event->event_conducted }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="editEvent({{ $event->id }})" 
                                    class="text-blue-600 hover:text-blue-900 mr-3 transition-colors">
                                Edit
                            </button>
                            <button wire:click="deleteEvent({{ $event->id }})" 
                                    onclick="return confirm('Are you sure you want to delete this event?')"
                                    class="text-red-600 hover:text-red-900 transition-colors">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No events found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $events->links() }}
    </div>

    <!-- Create/Edit Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" 
             x-data="{ show: @entangle('showCreateModal') }"
             x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-screen overflow-y-auto"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ $editingEvent ? 'Edit Event' : 'Create New Event' }}
                    </h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="{{ $editingEvent ? 'updateEvent' : 'createEvent' }}">
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Event Title</label>
                            <input wire:model="title" 
                                   type="text" 
                                   id="title"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea wire:model="description" 
                                      id="description"
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror"></textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Date and Time -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="event_date" class="block text-sm font-medium text-gray-700 mb-2">Event Date</label>
                                <input wire:model="event_date" 
                                       type="date" 
                                       id="event_date"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('event_date') border-red-500 @enderror">
                                @error('event_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="event_time" class="block text-sm font-medium text-gray-700 mb-2">Event Time</label>
                                <input wire:model="event_time" 
                                       type="time" 
                                       id="event_time"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('event_time') border-red-500 @enderror">
                                @error('event_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Location and Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                <input wire:model="location" 
                                       type="text" 
                                       id="location"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('location') border-red-500 @enderror">
                                @error('location') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="event_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select wire:model="event_conducted" 
                                        id="event_status"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('event_conducted') border-red-500 @enderror">
                                    <option value="Pending">Pending</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                                @error('event_conducted') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" 
                                wire:click="closeModal"
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            {{ $editingEvent ? 'Update Event' : 'Create Event' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<style>
    /* Custom styles for better UI */
    [x-cloak] { display: none !important; }
</style>

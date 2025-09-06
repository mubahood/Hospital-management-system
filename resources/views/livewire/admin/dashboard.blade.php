<div class="p-6">
    <!-- Real-time Stats Grid with Livewire -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Patients -->
        <div class="card stat-card p-6" wire:poll.5s="loadDashboardData">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Total Patients</p>
                    <p class="text-3xl font-bold">{{ number_format($totalPatients) }}</p>
                </div>
                <div class="bg-white bg-opacity-20 p-3 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Appointments Today -->
        <div class="card stat-card success p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Appointments Today</p>
                    <p class="text-3xl font-bold">{{ $appointmentsToday }}</p>
                </div>
                <div class="bg-white bg-opacity-20 p-3 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 9l6-6m0 0v6m0-6H6"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pending Events -->
        <div class="card stat-card warning p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm">Pending Events</p>
                    <p class="text-3xl font-bold">{{ $pendingEvents }}</p>
                </div>
                <div class="bg-white bg-opacity-20 p-3 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Critical Alerts -->
        <div class="card stat-card danger p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm">Critical Alerts</p>
                    <p class="text-3xl font-bold">{{ $criticalAlerts }}</p>
                </div>
                <div class="bg-white bg-opacity-20 p-3 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Activity with Livewire -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Recent Appointments -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Recent Appointments</h3>
                <button wire:click="refreshData" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg text-sm transition-colors">
                    Refresh
                </button>
            </div>
            <div class="space-y-4">
                @foreach($recentAppointments as $appointment)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div>
                            <p class="font-medium">{{ $appointment['doctor'] }} - {{ $appointment['patient'] }}</p>
                            <p class="text-sm text-gray-600">{{ $appointment['department'] }} • {{ $appointment['time'] }}</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs
                            @if($appointment['status'] == 'Confirmed') bg-green-100 text-green-800 
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ $appointment['status'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- System Status -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">System Status</h3>
            <div class="space-y-4">
                @foreach($systemStatus as $system)
                    <div class="flex items-center justify-between">
                        <span>{{ $system['name'] }}</span>
                        <span class="px-2 py-1 rounded-full text-xs
                            @if($system['status'] == 'Online' || $system['status'] == 'Up to date') bg-green-100 text-green-800 
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ $system['status'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Livewire Events Notifications -->
    <div class="fixed bottom-4 right-4 z-50" 
         x-data="{ show: false }" 
         x-on:data-refreshed.window="show = true; setTimeout(() => show = false, 3000)">
        <div x-show="show" 
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Data refreshed!
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        background: var(--surface);
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .stat-card {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
    }

    .stat-card.success {
        background: linear-gradient(135deg, var(--success-color), #059669);
    }

    .stat-card.warning {
        background: linear-gradient(135deg, var(--warning-color), #d97706);
    }

    .stat-card.danger {
        background: linear-gradient(135deg, var(--danger-color), #dc2626);
    }
</style>

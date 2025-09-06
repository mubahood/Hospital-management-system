<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public $stats = [];
    public $recentEvents = [];

    public function mount()
    {
        $this->loadStats();
        $this->loadRecentEvents();
    }

    public function loadStats()
    {
        $user = Auth::guard('admin')->user();
        $companyId = $user->company_id ?? 1; // Default company

        $this->stats = [
            'total_events' => Event::where('company_id', $companyId)->count(),
            'pending_events' => Event::where('company_id', $companyId)->where('event_conducted', 'Pending')->count(),
            'upcoming_events' => Event::where('company_id', $companyId)
                                    ->where('event_date', '>=', now())
                                    ->count(),
            'past_events' => Event::where('company_id', $companyId)
                                  ->where('event_date', '<', now())
                                  ->count(),
        ];
    }

    public function loadRecentEvents()
    {
        $user = Auth::guard('admin')->user();
        $companyId = $user->company_id ?? 1; // Default company

        $this->recentEvents = Event::where('company_id', $companyId)
            ->latest()
            ->take(5)
            ->get();
    }

    public function refreshData()
    {
        $this->loadStats();
        $this->loadRecentEvents();
        $this->emit('notify', 'Dashboard data refreshed successfully!', 'success');
    }

    public function render()
    {
        $this->loadStats();
        $this->loadRecentEvents();
        
        return view('livewire.dashboard');
    }
}

<?php

namespace App\Http\Livewire\Events;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class EventList extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $statusFilter = 'all';
    public $dateFilter = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => 'all'],
        'dateFilter' => ['except' => 'all'],
    ];

    public function mount()
    {
        // Initialize component
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->dateFilter = 'all';
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function deleteEvent($eventId)
    {
        try {
            $event = Event::where('company_id', Auth::guard('admin')->user()->company_id)
                          ->findOrFail($eventId);
            
            $event->delete();
            
            session()->flash('message', 'Event deleted successfully.');
            session()->flash('message_type', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Error deleting event: ' . $e->getMessage());
            session()->flash('message_type', 'error');
        }
    }

    public function toggleEventStatus($eventId)
    {
        try {
            $event = Event::where('company_id', Auth::guard('admin')->user()->company_id)
                          ->findOrFail($eventId);
            
            $event->update(['is_active' => !$event->is_active]);
            
            $status = $event->is_active ? 'activated' : 'deactivated';
            session()->flash('message', "Event {$status} successfully.");
            session()->flash('message_type', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Error updating event status: ' . $e->getMessage());
            session()->flash('message_type', 'error');
        }
    }

    public function render()
    {
        $query = Event::where('company_id', Auth::guard('admin')->user()->company_id);

        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            if ($this->statusFilter === 'active') {
                $query->where('is_active', true);
            } elseif ($this->statusFilter === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Apply date filter
        if ($this->dateFilter !== 'all') {
            $now = now();
            if ($this->dateFilter === 'upcoming') {
                $query->where('event_date', '>=', $now->toDateString());
            } elseif ($this->dateFilter === 'past') {
                $query->where('event_date', '<', $now->toDateString());
            } elseif ($this->dateFilter === 'today') {
                $query->whereDate('event_date', $now->toDateString());
            } elseif ($this->dateFilter === 'this_week') {
                $query->whereBetween('event_date', [
                    $now->startOfWeek()->toDateString(),
                    $now->endOfWeek()->toDateString()
                ]);
            } elseif ($this->dateFilter === 'this_month') {
                $query->whereMonth('event_date', $now->month)
                      ->whereYear('event_date', $now->year);
            }
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        $events = $query->paginate(10);

        return view('livewire.events.event-list', [
            'events' => $events
        ]);
    }
}

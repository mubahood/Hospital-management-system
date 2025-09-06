<?php

namespace App\Http\Livewire\Admin\Events;

use Livewire\Component;
use App\Models\Event;
use Livewire\WithPagination;

class EventList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $showCreateModal = false;
    public $editingEvent = null;
    
    // Form fields - matching existing Event model
    public $title = '';
    public $description = '';
    public $event_date = '';
    public $event_time = '';
    public $location = '';
    public $event_conducted = 'Pending';

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'event_date' => 'required|date',
        'event_time' => 'required',
        'location' => 'required|string|max:255',
        'event_conducted' => 'required|in:Pending,Completed,Cancelled'
    ];

    public function mount()
    {
        $this->event_date = now()->format('Y-m-d');
        $this->event_time = now()->format('H:i');
    }

    public function render()
    {
        $events = Event::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('event_conducted', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.events.event-list', [
            'events' => $events
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->editingEvent = null;
        $this->resetForm();
    }

    public function createEvent()
    {
        $this->validate();

        Event::create([
            'title' => $this->title,
            'description' => $this->description,
            'event_date' => $this->event_date,
            'event_time' => $this->event_time,
            'location' => $this->location,
            'event_conducted' => $this->event_conducted,
            'administrator_id' => 1, // Default admin user
            'company_id' => 1 // Default company
        ]);

        $this->closeModal();
        $this->emit('eventCreated');
        session()->flash('message', 'Event created successfully!');
    }

    public function editEvent($eventId)
    {
        $event = Event::find($eventId);
        if ($event) {
            $this->editingEvent = $event->id;
            $this->title = $event->title;
            $this->description = $event->description;
            $this->event_date = $event->event_date;
            $this->event_time = $event->event_time;
            $this->location = $event->location;
            $this->event_conducted = $event->event_conducted;
            $this->showCreateModal = true;
        }
    }

    public function updateEvent()
    {
        $this->validate();

        $event = Event::find($this->editingEvent);
        if ($event) {
            $event->update([
                'title' => $this->title,
                'description' => $this->description,
                'event_date' => $this->event_date,
                'event_time' => $this->event_time,
                'location' => $this->location,
                'event_conducted' => $this->event_conducted,
            ]);

            $this->closeModal();
            $this->emit('eventUpdated');
            session()->flash('message', 'Event updated successfully!');
        }
    }

    public function deleteEvent($eventId)
    {
        $event = Event::find($eventId);
        if ($event) {
            $event->delete();
            $this->emit('eventDeleted');
            session()->flash('message', 'Event deleted successfully!');
        }
    }

    public function resetForm()
    {
        $this->title = '';
        $this->description = '';
        $this->event_date = now()->format('Y-m-d');
        $this->event_time = now()->format('H:i');
        $this->location = '';
        $this->event_conducted = 'Pending';
        $this->resetErrorBag();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
}

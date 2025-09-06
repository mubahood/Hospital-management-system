<?php

namespace App\Http\Livewire\Events;

use Livewire\Component;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class EventShow extends Component
{
    public $event;
    public $eventId;

    public function mount($eventId)
    {
        $this->eventId = $eventId;
        $this->loadEvent();
    }

    public function loadEvent()
    {
        try {
            $this->event = Event::where('company_id', Auth::guard('admin')->user()->company_id)
                               ->findOrFail($this->eventId);
        } catch (\Exception $e) {
            session()->flash('message', 'Event not found.');
            session()->flash('message_type', 'error');
            return redirect()->route('app.events.index');
        }
    }

    public function toggleEventStatus()
    {
        try {
            $this->event->update(['is_active' => !$this->event->is_active]);
            $this->loadEvent(); // Refresh event data
            
            $status = $this->event->is_active ? 'activated' : 'deactivated';
            session()->flash('message', "Event {$status} successfully.");
            session()->flash('message_type', 'success');
        } catch (\Exception $e) {
            session()->flash('message', 'Error updating event status: ' . $e->getMessage());
            session()->flash('message_type', 'error');
        }
    }

    public function deleteEvent()
    {
        try {
            $this->event->delete();
            
            session()->flash('message', 'Event deleted successfully.');
            session()->flash('message_type', 'success');
            
            return redirect()->route('app.events.index');
        } catch (\Exception $e) {
            session()->flash('message', 'Error deleting event: ' . $e->getMessage());
            session()->flash('message_type', 'error');
        }
    }

    public function render()
    {
        return view('livewire.events.event-show');
    }
}

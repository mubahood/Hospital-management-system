<?php

namespace App\Http\Livewire\Events;

use Livewire\Component;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EventForm extends Component
{
    public $event;
    public $eventId;
    public $title;
    public $description;
    public $event_date;
    public $event_time;
    public $location;
    public $is_active = true;
    public $max_participants;
    public $registration_deadline;
    public $contact_email;
    public $contact_phone;
    public $notes;

    public $isEdit = false;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'event_date' => 'required|date|after_or_equal:today',
        'event_time' => 'nullable|date_format:H:i',
        'location' => 'nullable|string|max:255',
        'is_active' => 'boolean',
        'max_participants' => 'nullable|integer|min:1',
        'registration_deadline' => 'nullable|date|before_or_equal:event_date',
        'contact_email' => 'nullable|email|max:255',
        'contact_phone' => 'nullable|string|max:20',
        'notes' => 'nullable|string',
    ];

    protected $messages = [
        'title.required' => 'Event title is required.',
        'event_date.required' => 'Event date is required.',
        'event_date.after_or_equal' => 'Event date must be today or in the future.',
        'registration_deadline.before_or_equal' => 'Registration deadline must be before or on the event date.',
        'contact_email.email' => 'Please enter a valid email address.',
        'max_participants.min' => 'Maximum participants must be at least 1.',
    ];

    public function mount($eventId = null)
    {
        if ($eventId) {
            $this->eventId = $eventId;
            $this->isEdit = true;
            $this->loadEvent();
        } else {
            // Set default date to today
            $this->event_date = Carbon::today()->format('Y-m-d');
        }
    }

    public function loadEvent()
    {
        try {
            $this->event = Event::where('company_id', Auth::guard('admin')->user()->company_id)
                               ->findOrFail($this->eventId);

            $this->title = $this->event->title;
            $this->description = $this->event->description;
            $this->event_date = $this->event->event_date;
            $this->event_time = $this->event->event_time ? Carbon::parse($this->event->event_time)->format('H:i') : null;
            $this->location = $this->event->location;
            $this->is_active = $this->event->is_active;
            $this->max_participants = $this->event->max_participants;
            $this->registration_deadline = $this->event->registration_deadline;
            $this->contact_email = $this->event->contact_email;
            $this->contact_phone = $this->event->contact_phone;
            $this->notes = $this->event->notes;
        } catch (\Exception $e) {
            session()->flash('message', 'Event not found.');
            session()->flash('message_type', 'error');
            return redirect()->route('app.events.index');
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'title' => $this->title,
                'description' => $this->description,
                'event_date' => $this->event_date,
                'event_time' => $this->event_time,
                'location' => $this->location,
                'is_active' => $this->is_active,
                'max_participants' => $this->max_participants,
                'registration_deadline' => $this->registration_deadline,
                'contact_email' => $this->contact_email,
                'contact_phone' => $this->contact_phone,
                'notes' => $this->notes,
                'company_id' => Auth::guard('admin')->user()->company_id,
            ];

            if ($this->isEdit) {
                $this->event->update($data);
                $message = 'Event updated successfully.';
            } else {
                Event::create($data);
                $message = 'Event created successfully.';
            }

            session()->flash('message', $message);
            session()->flash('message_type', 'success');

            return redirect()->route('app.events.index');
        } catch (\Exception $e) {
            session()->flash('message', 'Error saving event: ' . $e->getMessage());
            session()->flash('message_type', 'error');
        }
    }

    public function cancel()
    {
        return redirect()->route('app.events.index');
    }

    public function resetForm()
    {
        $this->reset([
            'title', 'description', 'event_date', 'event_time', 'location',
            'max_participants', 'registration_deadline', 'contact_email',
            'contact_phone', 'notes'
        ]);
        $this->is_active = true;
        $this->event_date = Carbon::today()->format('Y-m-d');
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.events.event-form');
    }
}

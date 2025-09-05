<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use ApiResponser;

    public function __construct()
    {
        $this->middleware('auth:admin_api');
    }

    /**
     * Display a listing of events
     */
    public function index(Request $request)
    {
        $user = auth('admin_api')->user();
        
        $query = Event::where('company_id', $user->company_id)
                     ->orderBy('event_date', 'desc');

        // Apply filters
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->get('status') !== 'all') {
            $query->where('event_conducted', $request->get('status'));
        }

        $events = $query->paginate(20);

        return $this->success($events, 'Events retrieved successfully.');
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request)
    {
        $user = auth('admin_api')->user();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'location' => 'required|string|max:255',
        ]);

        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'location' => $request->location,
            'reminder_state' => $request->reminder_state ?? 'Off',
            'remind_beofre_days' => $request->remind_before_days ?? 1,
            'event_conducted' => 'Pending',
            'administrator_id' => $user->id,
            'company_id' => $user->company_id,
            'users_to_notify' => $request->users_to_notify ?? [],
            'images' => $request->images ?? [],
            'files' => $request->files ?? [],
        ]);

        return $this->success($event, 'Event created successfully.', 201);
    }

    /**
     * Display the specified event
     */
    public function show($id)
    {
        $user = auth('admin_api')->user();
        
        $event = Event::where('company_id', $user->company_id)->findOrFail($id);
        
        return $this->success($event, 'Event retrieved successfully.');
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, $id)
    {
        $user = auth('admin_api')->user();
        
        $event = Event::where('company_id', $user->company_id)->findOrFail($id);
        
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'event_date' => 'sometimes|required|date',
            'event_time' => 'sometimes|required',
            'location' => 'sometimes|required|string|max:255',
        ]);

        $event->update($request->only([
            'title', 'description', 'event_date', 'event_time', 'location',
            'reminder_state', 'remind_beofre_days', 'event_conducted',
            'users_to_notify', 'images', 'files'
        ]));

        return $this->success($event, 'Event updated successfully.');
    }

    /**
     * Remove the specified event
     */
    public function destroy($id)
    {
        $user = auth('admin_api')->user();
        
        $event = Event::where('company_id', $user->company_id)->findOrFail($id);
        
        $event->delete();
        
        return $this->success(null, 'Event deleted successfully.');
    }
}

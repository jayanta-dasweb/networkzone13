<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('events.index');
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'venue' => 'required|string|max:255',
            'number_of_seats' => 'required|integer',
            'ticket_price' => 'required|numeric'
        ]);

        $wallet = Auth::user()->wallet;

        if ($wallet->balance < 5) {
            return back()->withErrors(['error' => 'Insufficient balance in wallet']);
        }

        DB::beginTransaction();
        try {
            $event = Event::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'event_date' => $request->event_date,
                'event_time' => $request->event_time,
                'venue' => $request->venue,
                'number_of_seats' => $request->number_of_seats,
                'ticket_price' => $request->ticket_price
            ]);

            $wallet->balance -= 5;
            $wallet->save();

            DB::commit();

            return redirect()->route('events.index')->with('success', 'Event created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating event: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error creating event']);
        }
    }

    public function edit(Event $event)
    {
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'venue' => 'required|string|max:255',
            'number_of_seats' => 'required|integer',
            'ticket_price' => 'required|numeric'
        ]);

        $event->update($request->all());

        return redirect()->route('events.index')->with('success', 'Event updated successfully');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event deleted successfully');
    }

    public function getEvents(Request $request)
    {
        $columns = ['title', 'description', 'event_date', 'event_time', 'venue', 'number_of_seats', 'ticket_price'];
        $length = $request->input('length');
        $column = $request->input('column'); //Index
        $dir = $request->input('dir') === 'desc' ? 'desc' : 'asc'; //Direction
        $searchValue = $request->input('search');

        $query = Event::where('user_id', Auth::id())->orderBy($columns[$column], $dir);

        if ($searchValue) {
            $query->where(function($query) use ($searchValue) {
                $query->where('title', 'like', '%' . $searchValue . '%')
                    ->orWhere('description', 'like', '%' . $searchValue . '%')
                    ->orWhere('venue', 'like', '%' . $searchValue . '%');
            });
        }

        $events = $query->paginate($length);

        return response()->json([
            'data' => $events->items(),
            'draw' => $request->input('draw'),
            'recordsTotal' => $events->total(),
            'recordsFiltered' => $events->total(),
        ]);
    }
}


<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $events = Event::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        $wallet = Wallet::where('user_id', $user->id)->first();

        return view('events.index', [
            'events' => $events,
            'balance' => $wallet ? $wallet->balance : 0
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->first();

        return view('events.create', [
            'balance' => $wallet ? $wallet->balance : 0
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'venue' => 'required|string|max:255',
            'number_of_seats' => 'required|integer|min:1',
            'ticket_price' => 'required|numeric|min:0'
        ]);

        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->first();

        if ($wallet->balance < 5) {
            return back()->withErrors(['error' => 'Insufficient wallet balance.']);
        }

        $event = Event::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'venue' => $request->venue,
            'number_of_seats' => $request->number_of_seats,
            'ticket_price' => $request->ticket_price
        ]);

        // Deduct 5 USD from wallet and add transaction
        $wallet->balance -= 5;
        $wallet->save();

        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Event created: ' . $event->title,
            'amount' => -5
        ]);

        return redirect()->route('events.index')->with('success', 'Event created successfully.');
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);
        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->first();

        return view('events.edit', [
            'event' => $event,
            'balance' => $wallet ? $wallet->balance : 0
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'venue' => 'required|string|max:255',
            'number_of_seats' => 'required|integer|min:1',
            'ticket_price' => 'required|numeric|min:0'
        ]);

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'venue' => $request->venue,
            'number_of_seats' => $request->number_of_seats,
            'ticket_price' => $request->ticket_price
        ]);

        return redirect()->route('events.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->first();

        $event->delete();

        // Add 5 USD to wallet and add transaction
        $wallet->balance += 5;
        $wallet->save();

        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Event deleted: ' . $event->title,
            'amount' => 5
        ]);

        return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'event_ids' => 'required|array'
        ]);

        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->first();

        $events = Event::whereIn('id', $request->event_ids)->where('user_id', $user->id)->get();
        $deletedCount = 0;

        foreach ($events as $event) {
            $this->authorize('delete', $event);
            $event->delete();
            $deletedCount++;

            Transaction::create([
                'user_id' => $user->id,
                'description' => 'Event deleted: ' . $event->title,
                'amount' => 5
            ]);
        }

        // Add 5 USD for each deleted event to wallet and add transaction
        $wallet->balance += 5 * $deletedCount;
        $wallet->save();

        return redirect()->route('events.index')->with('success', 'Selected events deleted successfully.');
    }
}

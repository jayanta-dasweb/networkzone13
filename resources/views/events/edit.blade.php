@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Edit Event') }}</div>

                <div class="card-body">
                    <h4><strong>Wallet Balance: $<span id="wallet-balance">{{ $balance }}</span></strong></h4>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('events.update', $event) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="title" class="col-md-4 col-form-label text-md-end">{{ __('Title') }}</label>
                            <div class="col-md-6">
                                <input id="title" type="text" class="form-control" name="title" value="{{ old('title', $event->title) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="description" class="col-md-4 col-form-label text-md-end">{{ __('Description') }}</label>
                            <div class="col-md-6">
                                <textarea id="description" class="form-control" name="description" required>{{ old('description', $event->description) }}</textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="event_date" class="col-md-4 col-form-label text-md-end">{{ __('Event Date') }}</label>
                            <div class="col-md-6">
                                <input id="event_date" type="date" class="form-control" name="event_date" value="{{ old('event_date', $event->event_date) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="event_time" class="col-md-4 col-form-label text-md-end">{{ __('Event Time') }}</label>
                            <div class="col-md-6">
                                <input id="event_time" type="time" class="form-control" name="event_time" value="{{ old('event_time', $event->event_time->format('H:i')) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="venue" class="col-md-4 col-form-label text-md-end">{{ __('Venue') }}</label>
                            <div class="col-md-6">
                                <input id="venue" type="text" class="form-control" name="venue" value="{{ old('venue', $event->venue) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="number_of_seats" class="col-md-4 col-form-label text-md-end">{{ __('Number of Seats') }}</label>
                            <div class="col-md-6">
                                <input id="number_of_seats" type="number" class="form-control" name="number_of_seats" value="{{ old('number_of_seats', $event->number_of_seats) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="ticket_price" class="col-md-4 col-form-label text-md-end">{{ __('Ticket Price') }}</label>
                            <div class="col-md-6">
                                <input id="ticket_price" type="number" step="0.01" class="form-control" name="ticket_price" value="{{ old('ticket_price', $event->ticket_price) }}" required>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Update Event') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

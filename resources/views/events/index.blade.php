@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Events') }}</div>

                <div class="card-body">
                    <a href="{{ route('events.create') }}" class="btn btn-primary mb-3">Create Event</a>
                    <table id="events-table" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Event Date</th>
                                <th>Event Time</th>
                                <th>Venue</th>
                                <th>No. of Seats</th>
                                <th>Ticket Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {
        $('#events-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('events.data') }}',
            columns: [
                { data: 'title', name: 'title' },
                { data: 'description', name: 'description' },
                { data: 'event_date', name: 'event_date' },
                { data: 'event_time', name: 'event_time' },
                { data: 'venue', name: 'venue' },
                { data: 'number_of_seats', name: 'number_of_seats' },
                { data: 'ticket_price', name: 'ticket_price' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endsection

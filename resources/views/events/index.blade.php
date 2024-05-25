@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Events') }}</span>
                    <a href="{{ route('events.create') }}" class="btn btn-primary btn-sm">Create Event</a>
                </div>

                <div class="card-body">
                    <h4><strong>Wallet Balance: $<span id="wallet-balance">{{ $balance }}</span></strong></h4>
                    <table id="events-table" class="display">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Venue</th>
                                <th>Seats</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                            <tr>
                                <td><input type="checkbox" class="event-checkbox" data-id="{{ $event->id }}"></td>
                                <td>{{ $event->title }}</td>
                                <td>{{ $event->description }}</td>
                                <td>{{ $event->formatted_event_date }}</td>
                                <td>{{ $event->formatted_event_time }}</td>
                                <td>{{ $event->venue }}</td>
                                <td>{{ $event->number_of_seats }}</td>
                                <td>{{ $event->ticket_price }}</td>
                                <td>
                                    <a href="{{ route('events.edit', $event) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <button class="btn btn-danger btn-sm delete-event" data-id="{{ $event->id }}">Delete</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button id="delete-selected" class="btn btn-danger btn-sm">Delete Selected</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        var table = $('#events-table').DataTable();

        // Handle select all checkbox
        $('#select-all').on('click', function() {
            var rows = table.rows({ 'search': 'applied' }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });

        // Handle delete selected button
        $('#delete-selected').on('click', function() {
            var eventIds = [];
            $('.event-checkbox:checked').each(function() {
                eventIds.push($(this).data('id'));
            });

            if (eventIds.length > 0) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route("events.destroyMultiple") }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                event_ids: eventIds
                            },
                            success: function(response) {
                                Swal.fire(
                                    'Deleted!',
                                    'Your events have been deleted.',
                                    'success'
                                ).then(() => {
                                    if (response.redirect) {
                                        window.location.href = response.redirect;
                                    } else {
                                        window.location.reload();
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });

        // Handle individual delete button
        $('.delete-event').on('click', function() {
            var eventId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("events") }}/' + eventId,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            Swal.fire(
                                'Deleted!',
                                'Your event has been deleted.',
                                'success'
                            ).then(() => {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                } else {
                                    window.location.reload();
                                }
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endsection

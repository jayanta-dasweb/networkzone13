@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Events') }}</div>

                <div class="card-body">
                    <h4><strong>Wallet Balance: $<span id="wallet-balance">{{ $balance }}</span></strong></h4>
                    <table id="events-table" class="display">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                            <tr>
                                <td><input type="checkbox" class="event-checkbox" data-id="{{ $event->id }}"></td>
                                <td>{{ $event->date }}</td>
                                <td>{{ $event->name }}</td>
                                <td>
                                    <a href="{{ route('events.edit', $event) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('events.destroy', $event) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
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
                $.ajax({
                    url: '{{ route("events.destroyMultiple") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        event_ids: eventIds
                    },
                    success: function(response) {
                        window.location.reload();
                    }
                });
            }
        });
    });
</script>
@endsection

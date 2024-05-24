@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Wallet') }}</div>

                <div class="card-body">
                    <h4>Current Balance: ${{ Auth::user()->wallet->balance }}</h4>
                    <form id="add-funds-form">
                        @csrf
                        <div class="form-group">
                            <label for="amount">Amount to Add:</label>
                            <input type="number" class="form-control" id="amount" name="amount" min="5" required>
                        </div>
                        <div class="form-group">
                            <label for="card-element">Credit or Debit Card</label>
                            <div id="card-element" class="form-control"></div>
                            <div id="card-errors" role="alert"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Funds</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var stripe = Stripe('{{ config('services.stripe.key') }}');
        var elements = stripe.elements();
        var card = elements.create('card');
        card.mount('#card-element');

        card.addEventListener('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        $('#add-funds-form').on('submit', function(event) {
            event.preventDefault();
            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    stripeTokenHandler(result.token);
                }
            });
        });

        function stripeTokenHandler(token) {
            var form = $('#add-funds-form');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.append(hiddenInput);

            $.ajax({
                url: '{{ route('wallet.add') }}',
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Funds added successfully!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error adding funds'
                    });
                }
            });
        }
    });
</script>
@endsection

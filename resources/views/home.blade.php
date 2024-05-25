@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if ($wallet)
                        <h4>Current Balance: ${{ number_format($wallet->balance, 2) }}</h4>

                        <form id="add-funds-form">
                            @csrf
                            <div class="form-group">
                                <label for="amount">Amount to Add:</label>
                                <input type="number" class="form-control" id="amount" name="amount" min="5" value="5" required>
                            </div>
                            <div class="form-group">
                                <label for="card-element">Credit or Debit Card</label>
                                <div id="card-element" class="form-control"></div>
                                <div id="card-errors" role="alert"></div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Add Funds</button>
                            @if($wallet->balance >= 5)
                                <a href="{{ route('events.create') }}" class="btn btn-success ml-3 mt-3">Create Event</a>
                                <a href="{{ route('wallet.index') }}" class="btn btn-dark ml-3 mt-3 ">History</a>
                            @endif
                        </form>

                        @if ($wallet->balance < 5)
                            <p class="mt-3 text-danger">You need at least $5 in your wallet to create an event.</p>
                        @endif
                    @else
                        <p class="text-danger">Wallet information is not available. Please contact support.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://js.stripe.com/v3/"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        console.log("DOM fully loaded and parsed");

        var stripe = Stripe('{{ config('services.stripe.key') }}');
        console.log("Stripe initialized with key:", '{{ config('services.stripe.key') }}');

        var elements = stripe.elements();
        var card = elements.create('card');
        card.mount('#card-element');
        console.log("Card element mounted");

        card.addEventListener('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
                console.log("Stripe card error:", event.error.message);
            } else {
                displayError.textContent = '';
                console.log("Stripe card input valid");
            }
        });

        $('#amount').on('change', function() {
            var amount = $(this).val();
            if (amount < 5) {
                $(this).val(5);
            }
        });

        $('#add-funds-form').on('submit', function(event) {
            event.preventDefault();
            console.log("Form submit prevented, creating token...");
            showLoader(); // Show the loader

            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    hideLoader(); // Hide the loader on error
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                    console.log("Stripe token creation error:", result.error.message);
                } else {
                    console.log("Stripe token created successfully:", result.token.id);
                    createPaymentIntent(result.token);
                }
            });
        });

        function createPaymentIntent(token) {
            $.ajax({
                url: '{{ route('wallet.add') }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    amount: $('#amount').val(),
                    stripeToken: token.id
                },
                success: function(response) {
                    console.log("Server response:", response);
                    if (response.success) {
                        confirmPayment(response.clientSecret, $('#amount').val());
                    } else {
                        hideLoader(); // Hide the loader on error
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    hideLoader(); // Hide the loader on error
                    console.log("AJAX error:", xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error initiating payment'
                    });
                }
            });
        }

        function confirmPayment(clientSecret, amount) {
            stripe.confirmCardPayment(clientSecret).then(function(result) {
                if (result.error) {
                    hideLoader(); // Hide the loader on error
                    console.log("Payment confirmation error:", result.error.message);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.error.message
                    });
                } else {
                    if (result.paymentIntent.status === 'succeeded') {
                        completePayment(amount);
                    }
                }
            });
        }

        function completePayment(amount) {
            $.ajax({
                url: '{{ route('wallet.completePayment') }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    amount: amount
                },
                success: function(response) {
                    hideLoader(); // Hide the loader on success
                    console.log("Server response:", response);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
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
                    hideLoader(); // Hide the loader on error
                    console.log("AJAX error:", xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error completing payment'
                    });
                }
            });
        }

        function showLoader() {
            $("#loaderContainer").css("display","flex");
        }

        function hideLoader() {
            $("#loaderContainer").css("display","none");
        }
    });
</script>
@endsection

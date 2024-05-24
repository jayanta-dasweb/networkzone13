@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form id="payment-form">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                <div class="invalid-feedback" id="name-error"></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="email">
                                <div class="invalid-feedback" id="email-error"></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>
                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" required autocomplete="new-password">
                                <div class="invalid-feedback" id="password-error"></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>
                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                <div class="invalid-feedback" id="password-confirm-error"></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label id="captcha-label" for="captcha" class="col-md-4 col-form-label text-md-end">{{ $captcha }}</label>
                            <div class="col-md-6">
                                <input id="captcha" type="text" class="form-control" name="captcha_result" required>
                                <div class="invalid-feedback" id="captcha-error"></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="card-element" class="col-md-4 col-form-label text-md-end">Credit or Debit Card</label>
                            <div class="col-md-6">
                                <div id="card-element" class="form-control"></div>
                                <div id="card-errors" role="alert"></div>
                                <div class="invalid-feedback" id="payment-error"></div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
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

        function showLoader() {
            $("#loaderContainer").css("display", "flex");
        }

        function hideLoader() {
            $("#loaderContainer").css("display", "none");
        }

        function showError(errors) {
            if (errors.name) {
                $('#name').addClass('is-invalid');
                $('#name-error').text(errors.name[0] || errors.name).show();
            } else {
                $('#name').removeClass('is-invalid');
                $('#name-error').hide();
            }
            if (errors.email) {
                $('#email').addClass('is-invalid');
                $('#email-error').text(errors.email[0] || errors.email).show();
            } else {
                $('#email').removeClass('is-invalid');
                $('#email-error').hide();
            }
            if (errors.password) {
                $('#password').addClass('is-invalid');
                $('#password-error').text(errors.password[0] || errors.password).show();
            } else {
                $('#password').removeClass('is-invalid');
                $('#password-error').hide();
            }
            if (errors.password_confirmation) {
                $('#password-confirm').addClass('is-invalid');
                $('#password-confirm-error').text(errors.password_confirmation[0] || errors.password_confirmation).show();
            } else {
                $('#password-confirm').removeClass('is-invalid');
                $('#password-confirm-error').hide();
            }
            if (errors.captcha_result) {
                $('#captcha').addClass('is-invalid');
                $('#captcha-error').text(errors.captcha_result || errors.captcha_result).show();
            } else {
                $('#captcha').removeClass('is-invalid');
                $('#captcha-error').hide();
            }
            if (errors.payment) {
                $('#card-element').addClass('is-invalid');
                $('#payment-error').text(errors.payment[0] || errors.payment).show();
            } else {
                $('#card-element').removeClass('is-invalid');
                $('#payment-error').hide();
            }
        }

        $('#payment-form').on('submit', function(event) {
            event.preventDefault();
            console.log("Form submit prevented, creating Payment Intent...");
            showLoader(); // Show the loader

            $.ajax({
                url: '{{ route('register') }}',
                method: 'POST',
                data: {
                    _token: $('input[name="_token"]').val(),
                    name: $('#name').val(),
                    email: $('#email').val(),
                    password: $('#password').val(),
                    password_confirmation: $('#password-confirm').val(),
                    captcha_result: $('#captcha').val()
                },
                success: function(response) {
                    if (response.success) {
                        console.log("Payment Intent created, confirming payment...");
                        stripe.confirmCardPayment(response.clientSecret, {
                            payment_method: {
                                card: card,
                                billing_details: {
                                    name: $('#name').val(),
                                    email: $('#email').val()
                                }
                            }
                        }).then(function(result) {
                            if (result.error) {
                                console.log("Payment failed:", result.error.message);
                                hideLoader(); // Hide the loader on error
                                $.ajax({
                                    url: '{{ route('complete.registration') }}',
                                    method: 'POST',
                                    data: {
                                        _token: $('input[name="_token"]').val(),
                                        userId: response.userId,
                                        failed: true
                                    },
                                    success: function(response) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: response.message
                                        });
                                        regenerateCaptcha(response.captcha);
                                    }
                                });
                            } else {
                                if (result.paymentIntent.status === 'succeeded') {
                                    console.log("Payment succeeded, completing registration...");
                                    $.ajax({
                                        url: '{{ route('complete.registration') }}',
                                        method: 'POST',
                                        data: {
                                            _token: $('input[name="_token"]').val(),
                                            name: $('#name').val(),
                                            email: $('#email').val(),
                                            password: $('#password').val(),
                                            password_confirmation: $('#password-confirm').val(),
                                            captcha_result: $('#captcha').val(),
                                            userId: response.userId
                                        },
                                        success: function(response) {
                                            hideLoader(); // Hide the loader on success
                                            if (response.success) {
                                                Swal.fire({
                                                    icon: 'success',
                                                    title: 'Success',
                                                    text: 'Registration successful!'
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        window.location.href = '{{ route('home') }}';
                                                    }
                                                });
                                            } else {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Error',
                                                    text: response.message
                                                });
                                                regenerateCaptcha(response.captcha);
                                            }
                                        },
                                        error: function(xhr) {
                                            hideLoader(); // Hide the loader on error
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: 'Registration failed!'
                                            });
                                            regenerateCaptcha(response.captcha);
                                        }
                                    });
                                }
                            }
                        });
                    } else {
                        hideLoader(); // Hide the loader on error
                        showError(response.errors); // Show specific error messages
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Registration failed!'
                        });
                        regenerateCaptcha(response.captcha);
                    }
                },
                error: function(xhr) {
                    hideLoader(); // Hide the loader on error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Registration failed!'
                    });
                    regenerateCaptcha();
                }
            });
        });

        function regenerateCaptcha(captcha) {
            $('#captcha').val('');
            $('#captcha-label').text(captcha);
        }

        // Add event listeners for login and register forms
        $('#login-form').on('submit', function() {
            showLoader();
        });

        $('#register-form').on('submit', function() {
            showLoader();
        });
    });
</script>
@endsection

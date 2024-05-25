@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <form id="login-form" method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>
                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label id="captcha-label" for="captcha" class="col-md-4 col-form-label text-md-end">{{ $captcha }}</label>
                            <div class="col-md-6">
                                <input id="captcha" type="text" class="form-control @error('captcha_result') is-invalid @enderror" name="captcha_result" required>
                                @error('captcha_result')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>
                                <button type="button" class="btn btn-secondary" id="regenerate-captcha">
                                    {{ __('Regenerate Captcha') }}
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
<script>
    $(document).ready(function() {
        console.log("DOM fully loaded and parsed");

        $('#regenerate-captcha').on('click', function() {
            $.ajax({
                url: '{{ route('login.captcha.regenerate') }}',
                method: 'GET',
                success: function(response) {
                    console.log("Captcha regenerated:", response);
                    $('#captcha-label').text(response.captcha);
                    $('#captcha').val('');
                },
                error: function(xhr) {
                    console.log("Captcha regeneration failed");
                }
            });
        });

        $('#login-form').on('submit', function() {
            console.log("Form submitted");
        });
    });
</script>
@endsection

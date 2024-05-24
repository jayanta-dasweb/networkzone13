<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        /* loader style  */
        #loaderContainer {
            width: 100vw;
            height: 100vh;
            position: absolute;
            z-index: 99999;
            top: 0;
            left: 0;
            background-color: #00000011;
            display: none;
            align-items: center;
            justify-content: center;
        }


        /* HTML: <div class="loader"></div> */
        .loader {
            width: 80px;
            aspect-ratio: 1;
            border-radius: 50%;
            padding: 6px;
            background:
                conic-gradient(from 135deg at top, currentColor 90deg, #0000 0) 0 calc(50% - 4px)/17px 8.5px,
                radial-gradient(farthest-side at bottom left, #0000 calc(100% - 6px), currentColor calc(100% - 5px) 99%, #0000) top right/50% 50% content-box content-box,
                radial-gradient(farthest-side at top, #0000 calc(100% - 6px), currentColor calc(100% - 5px) 99%, #0000) bottom /100% 50% content-box content-box;
            background-repeat: no-repeat;
            animation: l11 1s infinite linear;
        }

        @keyframes l11 {
            100% {
                transform: rotate(1turn)
            }
        }
    </style>
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Loader -->
    <div id="loaderContainer">
        <div>
            <div class="loader" id="loader" ></div>
        </div>

    </div>


    <!-- Scripts -->
    @yield('scripts')
</body>

</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>GigDaemon</title>

        <!-- Scripts -->
        {{-- ИСПРАВЛЕНИЕ: Мы указываем правильный путь к нашему SCSS файлу, как и в основном шаблоне --}}
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div id="app">
            <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                <div class="container">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        GigDaemon
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <!-- Left Side Of Navbar -->
                        <ul class="navbar-nav me-auto">

                        </ul>

                        <!-- Right Side Of Navbar -->
                        <ul class="navbar-nav ms-auto">
                            <!-- Authentication Links -->
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
                        </ul>
                    </div>
                </div>
            </nav>

            <main class="py-4">
                <div class="container text-center">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header"><h1>Welcome to GigDaemon</h1></div>

                                <div class="card-body">
                                    <p>Your personal assistant for managing freelance projects.</p>
                                    <p>Please <a href="{{ route('login') }}">login</a> or <a href="{{ route('register') }}">register</a> to continue.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
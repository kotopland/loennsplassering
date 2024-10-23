<!doctype html>
<html lang="nb" dir="ltr">

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
    @vite(['resources/js/app.js'])

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.body.addEventListener('htmx:configRequest', (event) => {
                event.detail.headers['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
            })

        });
    </script>

    <script src='{{ url('js/_hyperscript.min.js') }}'></script>
    <style>
        .table-container {
            width: 100% !important;
            overflow-x: auto !important;
        }

        table {
            width: max-content !important;
            border-collapse: collapse !important;
        }

        th:first-child,
        td:first-child {
            position: sticky !important;
            left: 0 !important;
            /* background-color: white !important; */
        }

        .callout {
            padding: 20px;
            margin: 20px 0;
            border: 1px solid var(--bs-border-color);
            /* Use Bootstrap's border color */
            border-left-width: 5px;
            border-radius: 3px;
            background-color: var(--bs-body-bg);
            /* Default background */
        }

        .callout h4 {
            margin-top: 0;
            margin-bottom: 5px;
        }

        .callout-primary {
            background-color: var(--bs-primary-subtle);
            /* Bootstrap's subtle primary background */
            border-left-color: var(--bs-primary);
        }

        .callout-success {
            background-color: var(--bs-success-subtle);
            /* Subtle success background */
            border-left-color: var(--bs-success);
        }

        .callout-danger {
            background-color: var(--bs-danger-subtle);
            /* Subtle danger background */
            border-left-color: var(--bs-danger);
        }

        .callout-info {
            background-color: var(--bs-info-subtle);
            /* Subtle info background */
            border-left-color: var(--bs-info);
        }

        .callout-warning {
            background-color: var(--bs-warning-subtle);
            /* Subtle warning background */
            border-left-color: var(--bs-warning);
        }
    </style>
</head>

<body>
    <div id="app" class="container">
        <nav class="navbar navbar-expand-md shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
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
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item mr-auto">
                                    <a class="nav-link" href="{{ route('login') }}">
                                        <span class="iconify" data-icon="mdi:login"></span>
                                        @lang('Login')
                                    </a>
                                </li>
                            @endif

                            <li class="navUnreadNotifications-item nav-item mr-auto dropdown text-start" style="">

                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">

                                    <li class="nav-item mr-auto @if (request()->routeIs('privacy_policy')) active-menu @endif">
                                        <a class="dropdown-item" href="{{ route('welcome') }}">
                                            @lang('My privacy')
                                        </a>
                                    </li>

                                    <li><a class="dropdown-item" href="{{ route('welcome') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            @lang('Logout')
                                        </a></li>

                                </ul>
                                <form id="logout-form" action="{{ route('welcome') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>

                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container mb-0 pb-0 mt-3">
            {{-- {{ ($breadcrumb = Breadcrumbs::current()) ? Breadcrumbs::render(Route::currentRouteName()) : '' }} --}}
        </div>
        <main class="mb-4">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>

</html>

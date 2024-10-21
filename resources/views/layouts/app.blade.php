<!doctype html>
<html lang="{{ locale()->current() }}" dir="{{ locale()->dir() }}">

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
</head>

<body>
    <div id="app">
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

                            @if (Route::has('register'))
                                <li class="nav-item mr-auto">
                                    <a class="nav-link" href="{{ route('register') }}">
                                        <span class="iconify" data-icon="mdi:account-plus"></span>
                                        @lang('Register')
                                    </a>
                                </li>
                            @endif
                            @php $locale = request()->cookie('locale'); @endphp
                            <li class="nav-item dropdown">
                                <a id="navbarDropdownLocale" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                                    @switch($locale)
                                        @case('us')
                                            <span class="iconify" data-icon="bi:globe" data-inline="false"></span> EN
                                        @break

                                        @case('ja')
                                            <span class="iconify" data-icon="bi:globe" data-inline="false"></span> JA
                                        @break

                                        @default
                                            <span class="iconify" data-icon="bi:globe" data-inline="false"></span> EN
                                    @endswitch

                                    <span class="caret"></span>
                                </a>
                                {{-- <form class="form-inline navbar-select" action="{{ route('change_locale') }}" method="POST">
                                @csrf
                                <div class="form-group @if ($errors->first('locale')) has-error @endif">
                                    <span aria-hidden="true"><i class="fa fa-flag"></i></span>
                                    <select name="locale" id="locale" class="form-control form-select form-select-sm"
                                        required="required" onchange="this.form.submit()">
                                        <option value="nb" @if (session()->get('locale') == 'nb') selected="selected" @endif>
                                            NORSK</option>
                                        <option value="en" @if (session()->get('locale') == 'en') selected="selected" @endif>
                                            ENGLISH</option>
                                        <option value="ja" @if (session()->get('locale') == 'ja') selected="selected" @endif>
                                            日本語</option>
                                    </select>
                                    <small class="text-danger">{{ $errors->first('locale') }}</small>
                                </div>
                            </form> --}}
                                <ul class="dropdown-menu bg-secondary" aria-labelledby="navbarDropdownLocale" tabindex="0">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('change_locale', ['en']) }}">English</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('change_locale', ['ja']) }}">日本語</a>
                                    </li>
                                </ul>
                            </li>
                        @else
                            @php $locale = auth()->user()->locale; @endphp

                            <li class="nav-item dropdown">
                                <a id="navbarDropdownLocale" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                                    @switch($locale)
                                        @case('us')
                                            <span class="iconify" data-icon="bi:globe" data-inline="false"></span> EN
                                        @break

                                        @case('ja')
                                            <span class="iconify" data-icon="bi:globe" data-inline="false"></span> JA
                                        @break

                                        @default
                                            <span class="iconify" data-icon="bi:globe" data-inline="false"></span> EN
                                    @endswitch

                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdownLocale" tabindex="0">
                                    <li>
                                        <a class="dropdown-item" href="/lang/en">English</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="/lang/ja">日本語</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="navUnreadNotifications-item nav-item mr-auto dropdown text-start" style="">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{ Auth::user()->person->name ?? Auth::user()->email }}
                                </a>

                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">

                                    <li><a class="dropdown-item" href="{{ route('dashboard') }}">
                                            @lang('Profile')
                                        </a>
                                    </li>

                                    <li class="nav-item mr-auto @if (request()->routeIs('privacy_policy')) active-menu @endif">
                                        <a class="dropdown-item" href="{{ route('privacy_policy') }}">
                                            @lang('My privacy')
                                        </a>
                                    </li>

                                    <li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            @lang('Logout')
                                        </a></li>

                                </ul>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
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

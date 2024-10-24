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
    @yield('head')
</head>

<body>
    <div id="app" class="container">
        <div class="container">
            <div class="px-2 py-2 bg-primary-subtle border border-secondary border-2 border-top-0 border-start-0 border-end-0">
                <div class="row align-items-center">
                    <!-- Title Section -->
                    <div class="col-md-6 col-12">
                        <h4>{{ config('app.name', 'Laravel') }}</h4>
                    </div>

                    <!-- Button Section -->
                    <div class="col-md-auto col-12 mt-2 mt-md-0 text-md-end text-center">
                        @if (session('applicationId'))
                            <a href="#" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#yourModal">
                                Lagre dette skjemaet.
                            </a>
                        @endif
                    </div>
                </div>
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

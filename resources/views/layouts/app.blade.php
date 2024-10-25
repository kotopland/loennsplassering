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
            <div class="px-2 py-2 bg-primary-subtle border border-secondary border-4 border-top-0 border-start-0 border-end-0">
                <div class="row align-items-center">
                    <!-- Title Section -->
                    <div class="col-md-6 col-12">
                        <h4 style="line-height:0em;" class="pt-2"><strong>{{ config('app.name', 'Laravel') }}</strong></h4>
                    </div>

                    <!-- Button Section -->
                    <div class="col-md-auto col-12 mt-2 mt-md-0 text-md-end text-center">
                        @if (session('applicationId'))
                            <a href="#" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#yourModal">
                                Lagre skjemaet.
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <main class="my-4">
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
        <div class="modal fade" id="yourModal" tabindex="-1" aria-labelledby="yourModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">

                        <h1 class="modal-title fs-5" id="yourModalLabel">Modal title</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Du kan enten, <a href="{{ route('open-application', session('applicationId')) }}">lagre denne lenken</a> eller få lenken sendt til din e-postadresse.
                        <div class=" my-4">
                            <label for="email" class="form-label">Send til e-postaddressen:</label>
                            <input type="email" class="form-control" id="email" name="email_address" placeholder="skriv inn en e-postadresse"="emailHelp">
                            <div id="emailHelp" class="form-text">Vi lagrer ikke e-postadressen din.</div>
                        </div>
                        <div class="pt-2 ps-2 text-primary" id="email-result"></div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" hx-post="{{ route('send-application-link-to-email', session('applicationId')) }}" hx-trigger="click" hx-include="[name='email_address']" hx-target="#email-result">Send</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="cookie-banner" class="cookie-banner">
            <p>
                Dette nettstedet bruker informasjonskapsler for å sikre at du får den beste opplevelsen på nettstedet vårt.
                <a href="/privacy-policy" target="_blank">Finn ut mer</a>
            </p>
            <div class="cookie-buttons">
                <button id="accept-cookies" class="btn btn-success my-3 me-4">Aksept</button>
                <button id="reject-cookies" class="btn btn-danger my-3">Avvis</button>
            </div>
        </div>
</body>

</html>

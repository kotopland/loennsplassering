<!doctype html>
<html lang="nb" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="content-language" content="no">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description" content="Beregn din sannsynlige lønnsplassering i Frikirken basert på stillingstittel, utdanning og erfaring. Få en indikasjon på lønnstrinn og last ned en oversikt.">
    <meta name="keywords" content="lønnskalkulator, lønn, frikirken, lønnstrinn, stilling, utdanning, erfaring, ansiennitet, kompetansepoeng">
    <meta property="og:title" content="Lønnskalkulator - Frikirken">
    <meta property="og:description" content="Beregn din sannsynlige lønnsplassering i Frikirken basert på stillingstittel, utdanning og erfaring. Få en indikasjon på lønnstrinn og last ned en oversikt.">
    <meta property="og:url" content="{{ config('app.url') }}">
    <meta property="og:locale" content="nb_NO">
    <meta property="og:type" content="website">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    {{--
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet"> --}}

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
    <div class="container-fluid bg-secondary text-white py-3">
        <div class="row align-items-center">
            <!-- Header Content -->
            <div class="col-12 d-flex flex-wrap align-items-center justify-content-between pe-md-5 ps-md-5">
                <!-- Logo Section -->
                <div class="d-flex align-items-center">
                    {{-- <a href="{{ url('https://www.frikirken.no') }}" class="text-decoration-none"> --}}
                    <img src="{{ url('images/logo-frikirken-w.png') }}" alt="Gå til Frikirkens nettside" class="img-fluid py-2" style="max-height: 65px">
                    {{-- </a> --}}
                </div>

                <!-- Button Section -->
                <div class="d-flex align-items-center gap-2">
                    @if (session('applicationId'))
                        <a href="{{ route('welcome') }}" class="btn btn-sm btn-outline-light my-1">
                            Forsiden
                        </a>

                        <a href="#" class="btn btn-sm btn-outline-light my-1" data-bs-toggle="modal" data-bs-target="#yourModal">
                            Lagre skjemaet
                        </a>

                        <a href="{{ route('signout') }}" class="btn btn-sm btn-outline-light my-1" onclick="if (!confirm('Har du husket å lagre dette skjemaet og fått lenken til på e-post? Svarer du ja/ok logges du ut.')) return false;">
                            Logg ut
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div id="app" class="container">
        <div class="container">
            @auth
                Administrere:
                <form action="{{ route('logout') }}" method="POST">
                    @csrf <a href="{{ route('admin.positions.index') }}">Stillinger</a> -
                    <a href="{{ route('admin.salary-ladders.index') }}">Lønnsstiger</a>
                    <button type="submit" class="btn btn-link">Logg ut av admin verktøyet</button>
                </form>
            @endauth

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
                <h1>{{ config('app.name', 'Laravel') }}<img src="{{ url('images/clarity--beta-line.png') }}" width="50px" style="margin-top: -40px"></h1>
                @if (is_null(request()->cookie('cookie_consent')) || request()->cookie('cookie_consent') !== 'rejected')
                    @yield('content')
                @else
                    Du kan ikke bruke denne webappen uten å akseptere informasjonskapsler (cookies).
                    Dersom du ønsker å bruke webappen, kan du ombestemme deg og <div class="cookie-buttons"><button id="accept-cookies" class="btn btn-outline-primary my-3 me-4" _="on click wait 500ms then reload() the location of the window">akseptere nødvendige
                            informasjonskapsler.</button></div>
                @endif
            </main>

        </div>
        <div class="modal fade" id="yourModal" tabindex="-1" aria-labelledby="yourModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">

                        <h1 class="modal-title fs-5" id="yourModalLabel">Ta vare på skjemaet</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Bokmerk denne siden eller få en lenke til din e-postadresse:
                        <div class="pt-2 ps-2 text-success" id="email-result">
                            <div class=" my-4">
                                <input type="email" class="form-control" id="email-input" name="email_address" required placeholder="Din e-postadresse...">
                                <div id="emailHelp" class="form-text">Vi lagrer ikke e-postadressen din.</div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" hx-post="{{ route('send-application-link-to-email', session('applicationId')) }}" hx-trigger="click" hx-include="[name='email_address']" hx-target="#email-result" hx-validate="true" _="on click wait 500ms then remove me">Send</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="cookie-banner" class="cookie-banner">
            <p>
                Dette nettstedet bruker informasjonskapsler for å sikre at du får den beste opplevelsen på nettstedet
                vårt.
                <a href="{{ route('privacy-policy') }}">Finn ut mer.</a>
            </p>
            <div class="cookie-buttons">
                <button id="accept-cookies" class="btn btn-primary border border-light rounded my-3 me-4" _="on click wait 500ms then reload() the location of the window">Aksept</button>
                <button id="reject-cookies" class="btn btn-outline-danger my-3" _="on click wait 500ms then reload() the location of the window">Avvis</button>
            </div>
        </div>
    </div>
    <div class="row bg-primary text-center py-3 px-5 mt-5 text-light">
        <div class="col-6 text-start">
            <div class="row">
                <div class="col-auto">© Copyright 2024 {{ Date('Y') == 2024 ? '' : '- ' . Date('Y') }}</div>
                <div class="col"> Evangelisk
                    Lutherske Frikirke
                </div>
            </div>
        </div>
        <div class="col-6 text-end">
            <small>
                <a class="text-light" href="{{ route('privacy-policy') }}">Personvernerklæring</a>
                -
                <a class="text-light" href="{{ url('www.frikirken.no') }}">www.frikirken.no</a>
                -
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 256 256">
                    <path fill="currentColor" d="M216 40H40a16 16 0 0 0-16 16v144a16 16 0 0 0 16 16h176a16 16 0 0 0 16-16V56a16 16 0 0 0-16-16M92.8 145.6a8 8 0 1 1-9.6 12.8l-32-24a8 8 0 0 1 0-12.8l32-24a8 8 0 0 1 9.6 12.8L69.33 128Zm58.89-71.4l-32 112a8 8 0 1 1-15.38-4.4l32-112a8 8 0 0 1 15.38 4.4m53.11 60.2l-32 24a8 8 0 0 1-9.6-12.8l23.47-17.6l-23.47-17.6a8 8 0 1 1 9.6-12.8l32 24a8 8 0 0 1 0 12.8" />
                </svg><small>Topland</small>
        </div>
    </div>
</body>

</html>

@extends('layouts.app')

@section('content')
    @if (session()->has('message'))
        <p class="alert {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
    @endif

    <div id="intro" class="callout callout-secondary ">
        <h2 style="margin-top:0!important;">Før du starter</h2>
        <div class="my-2">
            Før du begynner å bruke dette verktøyet må du samle inn informasjon om stillingen som du skal inn i og din utdannelse og erfaring.
        </div>
        <div class="my-2">
            <a class="btn btn-success" href="#" _="on click remove .d-none from #steg then remove #intro">Fortsett om du har det klart.</a>
        </div>
    </div>
    <div id="steg" class="d-none">
        <div id="intro" class="callout callout-secondary ">
            <h2 style="margin-top:0!important;">Stegene for å få beregnet en lønnsplassering </h2>
            Les alt under før du går videre ved å trykke på knappen på bunnen av siden.
        </div>
        <div class="step">
            <div>
                <div class="step-circle">1</div>
            </div>
            <div>
                <div class="step-title">
                    <h2 class="mb-4">Informasjon om stillingen</h2>
                </div>
                <div class="step-caption">
                    <ul>
                        <li>Hva du skal arbeide som.</li>
                        <li>Din fødselsdato.</li>
                        <li>Når du skal starte å arbeide.</li>
                    </ul>
                </div>
                <hr />
            </div>
        </div>
        <div class="step">
            <div>
                <div class="step-circle">2</div>
            </div>
            <div>
                <div class="step-title">
                    <h2 class="mb-4">Din kompetanse</h2>
                </div>
                <div class="step-caption">

                    <ul>
                        <li>Studie og studiested</li>
                        <li>Studiepoeng og varighet</li>
                        <li>Relevanse for stilingen</li>
                    </ul>
                </div>
                <hr />
            </div>
        </div>
        <div class="step">
            <div>
                <div class="step-circle">3</div>
            </div>
            <div>
                <div class="step-title">
                    <h2 class="mb-4">Din arbeidserfaring</h2>
                </div>
                <div class="step-caption">

                    <ul>
                        <li>Tittel og arbeidssted</li>
                        <li>Stillingsprosent og varighet</li>
                        <li>Relevanse for stilingen som du skal tre inn i </li>
                    </ul>
                </div>
                <hr />
            </div>
        </div>
        <div class="step">
            <div>
                <div class="step-circle">4</div>
            </div>
            <div>
                <div class="step-title">
                    <h2 class="mb-4">Forhåndsvisning og nedlasting</h2>
                </div>
                <div class="step-caption">
                    Vi har fått nok opplysninger og du får presentert en midlertidig plassering av lønnen din. I dette steget er det viktig å laste ned Excel skjema. Dette excel skjemaet kan brukes til å sende inn til Frikirken for en endelig lønnsplassering

                    <ul>
                        <li>Kompetansepoeng og din midlertidige lønnsplassering</li>
                        <li>Tidsskala over din utdannelse og erfaring som definerer lønnsplasseringen</li>
                    </ul>
                </div>
                <hr />
            </div>
        </div>
        <div class="my-5 py-5">
            <a class="btn btn-success btn-lg" href="{{ route('enter-employment-information') }}">Start utfylling av skjemaet</a>
        </div>

    </div>
@endsection

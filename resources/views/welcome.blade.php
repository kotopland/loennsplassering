@extends('layouts.app')

@section('content')
@if (session()->has('message'))
<p class="alert {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
@endif
<div class="callout callout-secondary bg-info">
    <p class="teaser">
        Lurer du på hva lønnen din bør være?
        Denne kalkulatoren gir deg en indikasjon på lønnsplasseringen din basert på
        stillingstittel, utdanningsnivå og relevant arbeidserfaring.
    </p>
    <p>
        I Frikirken følger alle ansettelser lønnsavtalen vedtatt av synodestyret (<a
            href="{{ url('https://frikirken.no/arbeid') }}">https://frikirken.no/arbeid</a>).
        Lønnsplasseringen foretas av sekretæren for lønnsutvalget, basert på lønnsavtalen utifra arbeidsgivers
        forventninger
        og
        den ansattes kvalifikasjoner.
        Dette er en tidkrevende prosess, og lønnsplasseringen skjer derfor vanligvis etter ansettelsen.
    </p>
    <h3>Hva verktøyet gjør</h3>
    For å få en
    indikasjon på hva lønnen blir, kan du bruke dette verktøyet for å beregne en omtrentlig lønnsplassering.
    Den vil:
    <ul>
        <li>Beregne kompetansepoeng og ansiennitet, og få din plassering i statens lønnstabell.</li>
        <li>Se en tidslinje over din kompetanse og ansiennitet.</li>
        <li>Se en tidslinje over maskinberegnet kompetanse og ansiennitet basert på lønnsavtalen.</li>
        <li>Motta en ferdig utfylt oversikt med beregnet lønnsplassering som et Excel-vedlegg på e-post.</li>
    </ul>
    <h3>Begrensninger i verktøyet</h3>
    Vær oppmerksom på at dette verktøyet ikke tar hensyn til
    <ul>
        <li> kurs, verv og frivillig arbeid som kan i noen tilfeller gi tillegg. </li>
        <li>arbeidsgivers individuelle vurderinger av kompetanse, ansiennitet og ansvar.</li>
    </ul>
    <strong>Den estimerte lønnsplasseringen er derfor ikke endelig og kan ikke brukes som argumentasjon i
        plasseringen. Avvik på 1-2 lønnstrinn må forventes, både opp og ned.</strong>
    <h3>Håndtering av dine data</h3>
    Denne beregningen er anonym. Din e-postadresse lagres ikke, men brukes kun til å sende deg resultatet som en
    Excel-fil.
    Slik kan du enkelt hente frem beregningen senere.
    <br />Informasjonen du registrerer i skjemaet lagres i en database, slik at du også kan hente den frem igjen ved
    behov. Det brukes også for lønnsutvalget til å forbedre og finjustere verktøyet.
</div>

@if (session('applicationId'))
<div class="text-md-end text-center pb-1">
    <a href="{{ route('enter-employment-information') }}" class="btn btn-lg btn-primary my-2 me-3">Fortsett med ditt
        registrete lønnsplasseringsskjema</a>
    <a href="{{ route('enter-employment-information', ['createNew' => true]) }}"
        _="on click if not confirm('Vil du starte på nytt? Ønsker du å beholde skjemaet, må du først bokmerke eller få sendt skjemaet til din e-post adresse ved å trykke på knappen øverst på denne siden') halt"
        class="btn btn-lg btn-outline-primary my-2">Start på nytt<br /><small><small>med et tomt
                skjema</small></small></a>
</div>
@else
<div class="text-center">
    <a href="{{ is_null(request()->cookie('cookie_consent')) ? '#' : route('enter-employment-information', ['createNew' => true]) }}"
        class="btn btn-lg btn-primary @if (is_null(request()->cookie('cookie_consent'))) disabled @endif">Start
        her for å
        beregne lønnsplassering</a>
</div>
@endif

<div class="mb-5"></div>

<div class="callout callout-secondary bg-info">
    <h3 style="margin-top:0!important;">Har allerede et tidligere utfylt lønnsplasseringsskjema?</h3>
    Hvis du har et lønnsskjema i samme format som du finner på Frikirkens websider kan du laste det opp slik at du
    kan
    arbeide videre med det og gjøre en beregning av lønnsplasseringen din. Vi lagrer ikke dokumentet og bare
    stilling,
    fødselsdato, tiltredelsesdato, kompetanse og ansiennitet blir benyttet.
    <form action="{{ is_null(request()->cookie('cookie_consent')) ? '#' : route('loadExcel') }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="my-3">
            <label for="excelFile" class="form-label">Last opp et allerede fylt ut Frikirkens lønnsskjema (Excel
                fil)</label>
            <input type="file" name="excel_file" id="excelFile" required class="form-control">
        </div>
        <div class="text-md-end text-center pb-1">
            @if (session('applicationId'))
            <button type="submit"
                class="btn btn-lg btn-outline-primary @if (is_null(request()->cookie('cookie_consent'))) disabled @endif">
                Last opp og bruk dette skjemaet <br /><small>(avslutter skjemaet som du allerede holder på
                    med)</small>
            </button>
            @else
            <button type="submit"
                class="btn btn-lg btn-primary @if (is_null(request()->cookie('cookie_consent'))) disabled @endif">
                Last inn og fortsett utfylling
            </button>
            @endif
        </div>
    </form>
</div>
@endsection
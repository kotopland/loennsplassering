@extends('layouts.app')

@section('content')
    @if (session()->has('message'))
        <p class="alert {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
    @endif

    <div class="callout callout-primary bg-primary-subtle">
        <h3 style="margin-top:0!important;">Hva gjør dette verktøyet?</h3>
        Alle ansettelser i Frikirken følger lønnsavtalen vedtatt av synodestyret (<a href="{{ url('https://frikirken.no/arbeid') }}">https://frikirken.no/arbeid</a>).
        Sekretæren for lønnsutvalget foretar lønnsplasseringer i henhold til lønnsavtalen, arbeidsgivers forventninger til stillingen og den ansattes kvalifikasjoner.
        Dette er en tidkrevende prosess, og det er derfor vanlig praksis å foreta lønnsplasseringen etter ansettelsen.
        Dette kan oppleves som ugunstig for ansatte, og vi har derfor utviklet et verktøy som kan beregne en sannsynlig lønnsplassering.
        Ved å bruke dette verktøyet får du
        <li>beregnet kompetansepoeng, ansiennitet og plassering i statens lønnstabell</li>
        <li>se en tidslinje over din kompetanse og ansiennitet</li>
        <li>se en tidslinje over maskinberegnet kompetanse og ansiennitet etter regler fra lønnsavtalen</li>
        <li>få en ferdig utfylt og med beregnet lønnsplassering i form av et Excel vedlegg på e-post</li>
        <h3>Begrensninger i verktøyet</h3>
        Verktøyet tar ikke hensyn til arbeidsgivers forventninger og vurderinger av relevant kompetanse og ansiennitet for stillingen. Dette skaper usikkerhet i plasseringen. Plasseringen verktøyet gir er derfor ikke endelig og kan ikke brukes som argumentasjon dersom lønnsplasseringen ikke samsvarer med den ansattes forventninger. Det kan forekomme avvik på 1-2 lønnstrinn, både opp og ned.
        <h3>Håndtering av dine data</h3>
        Denne beregningen er anonym. Når du blir spurt om å legge inn e-postadressen din, blir den ikke lagret, men brukes til å sende deg skjemaet ditt eller beregnet lønnsplassering som excel fil slik at du kan hente det frem senere. Informasjonen du ellers registrerer lagres i databasen slik at du kan hente den frem igjen. Databasen vil bli brukt til å finjustere verktøyet.
    </div>

    @if (session('applicationId'))
        <div class="text-md-end text-center pb-1">
            <a href="{{ route('enter-employment-information') }}" class="btn btn-lg btn-success my-2">Fortsett med ditt registrete lønnsplasseringsskjema</a>
            <a href="{{ route('enter-employment-information', ['createNew' => true]) }}" _="on click if not confirm('Vil du starte på nytt? Ønsker du å beholde skjemaet, må du først bokmerke eller få sendt skjemaet til din e-post adresse ved å trykke på knappen øverst på denne siden') halt" class="btn btn-lg btn-secondary my-2">Start med et tomt skjema</a>
        </div>
    @else
        <a href="{{ is_null(request()->cookie('cookie_consent')) ? '#' : route('enter-employment-information', ['createNew' => true]) }}" class="btn btn-lg btn-success @if (is_null(request()->cookie('cookie_consent'))) disabled @endif">Start her for å beregne lønnsplassering</a>
    @endif

    <div class="mb-5"></div>

    <div class="callout callout-secondary">
        <h3 style="margin-top:0!important;">Har allerede et tidligere utfylt lønnsplasseringsskjema?</h3>
        Hvis du har et lønnsskjema i samme format som du finner på Frikirkens websider kan du laste det opp slik at du kan arbeide videre med det og gjøre en beregning av lønnsplasseringen din. Vi lagrer ikke dokumentet og bare stilling, fødselsdato, tiltredelsesdato, kompetanse og ansiennitet blir benyttet.
        <form action="{{ is_null(request()->cookie('cookie_consent')) ? '#' : route('loadExcel') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="my-3">
                <label for="excelFile" class="form-label">Last opp et allerede fylt ut Frikirkens lønnsskjema (Excel fil)</label>
                <input type="file" name="excel_file" id="excelFile" required class="form-control">
            </div>
            <div class="text-md-end text-center pb-1">
                <button type="submit" class="btn btn-lg btn-primary @if (is_null(request()->cookie('cookie_consent'))) disabled @endif">
                    @if (session('applicationId'))
                        Last opp og bruk dette skjemaet <br /><small>(avslutter skjemaet som du allerede holder på med)</small>
                    @else
                        Last inn og fortsett utfylling
                    @endif
                </button>
            </div>
        </form>
    </div>
@endsection

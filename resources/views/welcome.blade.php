@extends('layouts.app')

@section('content')
    @if (session()->has('message'))
        <p class="alert {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
    @endif

    <h1>Få et anslag på din lønn</h1>
    <div class="callout callout-primary bg-primary-subtle">
        <h3 style="margin-top:0!important;">Hva gjør dette verktøyet?</h3>
        Alle ansatte i Frikirken følger Frikirkens synodestyrets vedtatte Lønnsavtale. Lønnsberegninger må gjøres manuelt da det er mange faktorer som spiller inn og arbeidsgivers forventninger av innhold i stillingen og den ansattes kvalifikasjoner.
        <h3>Begrensninger i verktøyet</h3>
        Dette verktøyet gjør det mulig å beregne en ca lønnsplassering utifra det som står i lønnsavtalen. Det er ikke en endelig. Det kan være avik på 1-2 lønnstrinn og det kan slå begge veier.
        <h3>Håndtering av dine data</h3>
        Denne beregningen er anonym. E-post adressen som vi spør om i første bilde, lagres ikke, men sender deg en lenke til ditt skjema slik at du kan hente det ved en senere anledning. Det som du ellers registrere blir lagret i databasen for at du kan hente det tilbake.
        Databasen vil bli brukt for å finjsuteret verktøyet.
    </div>

    @if (session('applicationId'))
        Hent din siste registrete lønnsplasseringsskjema
    @else
    @endif
    <a href="{{ route('enter-employment-information') }}" class="btn btn-lg btn-success">Start her for å beregne lønnsplassering</a>

    <div class="mb-5"></div>

    <div class="callout callout-primary bg-primary-subtle">
        <h3 style="margin-top:0!important;">Har allerede et tidligere utfylt lønnsplasseringsskjema?</h3>
        Hvis du har et lønnsskjema i samme format som du finner på Frikirkens websider kan du laste det opp slik at du kan arbeide videre med det og gjøre en beregning av lønnsplasseringen din.
    </div>
    <form action="{{ route('loadExcel') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="excelFile" class="form-label">Last opp et allerede fylt ut Frikirkens lønnsskjema (Excel fil)</label>
            <input type="file" name="excel_file" id="excelFile" required class="form-control">
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Last inn og fortsett utfylling</button>
        </div>
    </form>
@endsection

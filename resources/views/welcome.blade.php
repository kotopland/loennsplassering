@extends('layouts.app')

@section('content')
    @if (session()->has('message'))
        <p class="alert my-2 {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
    @endif

    <div class="">
        <h1>Beregn din lønnsplassering</h1>
        <p class="small">Oppdatert etter 2024 forhandlingene</p>
        <p class="teaser">
            Lurer du på hva lønnen din bør være?
            Denne kalkulatoren gir deg en indikasjon på lønnsplasseringen din basert på
            stillingstittel, utdanningsnivå og relevant arbeidserfaring.
        </p>
        <div class="accordion " id="accordionInfoBox">
            <div class="accordion-item">
                <h5 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        <strong>Lønnsplassering følger Frikirkens lønnsavtale</strong>
                    </button>
                </h5>
                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionInfoBox">
                    <div class="accordion-body">
                        <p>
                            I Frikirken følger alle ansettelser lønnsavtalen vedtatt av synodestyret (<a href="{{ url('https://frikirken.no/arbeid') }}">https://frikirken.no/arbeid</a>).
                            Lønnsplasseringen foretas av sekretæren for lønnsutvalget, basert på lønnsavtalen utifra arbeidsgivers
                            forventninger
                            og
                            den ansattes kvalifikasjoner.
                            Dette er en tidkrevende prosess, og lønnsplasseringen skjer derfor vanligvis etter ansettelsen.
                        </p>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h5 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        <strong>Hva gjør verktøyet</strong>
                    </button>
                </h5>
                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionInfoBox">
                    <div class="accordion-body">
                        For å få en
                        indikasjon på hva lønnen blir, kan du bruke dette verktøyet for å beregne en omtrentlig lønnsplassering.
                        Den vil:
                        <ul>
                            <li>Beregne kompetansepoeng og ansiennitet, og få din plassering i statens lønnstabell.</li>
                            <li>Se en tidslinje over din kompetanse og ansiennitet.</li>
                            <li>Se en tidslinje over maskinberegnet kompetanse og ansiennitet basert på lønnsavtalen.</li>
                            <li>Motta en ferdig utfylt oversikt med beregnet lønnsplassering som et Excel-vedlegg på e-post.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h5 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        <strong>Hva gjør ikke verktøyet</strong>
                    </button>
                </h5>
                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionInfoBox">
                    <div class="accordion-body">
                        Vær oppmerksom på at dette verktøyet ikke tar hensyn til
                        <ul>
                            <li> kurs, verv og frivillig arbeid som kan i noen tilfeller gi tillegg. </li>
                            <li>arbeidsgivers individuelle vurderinger av kompetanse, ansiennitet og ansvar.</li>
                        </ul>
                        <strong>Den estimerte lønnsplasseringen er derfor ikke endelig og kan ikke brukes som argumentasjon i
                            plasseringen. Avvik på 1-2 lønnstrinn må forventes, både opp og ned.</strong>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h5 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                        <strong>Håndtering av dine data</strong>
                    </button>
                </h5>
                <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionInfoBox">
                    <div class="accordion-body">
                        Frem til og med beregning av lønnsplassering vil personopplysninger som navn, adresse, overordnede og epost adresse ikke bli lagret.
                        Når du velger å trykke på "Fyll ut Personalia og Send inn for behandling" på siste side av skjemaet, vil det som du har sendt inn bli lagret. Det blir lagret slik at det er nok informasjon og kontaktopplysninger for at Frikirken kan behandle ditt lønnsskjema.

                        <br />Informasjonen du registrerer i skjemaet lagres i en database, slik at du også kan hente den frem igjen ved
                        behov. Det brukes også for lønnsutvalget og dem de bemyndiger til å behandle lønnsplasseringen og til utvikler for å forbedre og finjustere verktøyet.
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('applicationId'))
        <div class="text-md-end text-center pb-1">
            <a href="{{ route('enter-employment-information') }}" class="btn btn-lg btn-primary my-4 me-3" _="on click put 'Vennligst vent...' into my.innerHTML then wait 20ms then add .disabled to me end">Fortsett med ditt
                registrete lønnsplasseringsskjema</a>
            <a href="{{ route('enter-employment-information', ['createNew' => true]) }}" _="on click if not confirm('Vil du starte på nytt? Ønsker du å beholde skjemaet, må du først bokmerke eller få sendt skjemaet til din e-post adresse ved å trykke på knappen øverst på denne siden') halt else put 'Vennligst vent...' into my.innerHTML then wait 20ms then add .disabled to me end" class="btn btn-lg btn-outline-primary my-2">Start på nytt<br /><small><small>med et tomt
                        skjema</small></small></a>
        </div>
    @else
        <div class="text-center">
            <a href="{{ is_null(request()->cookie('cookie_consent')) ? '#' : route('enter-employment-information', ['createNew' => true]) }}" class="btn btn-lg btn-primary my-4 @if (is_null(request()->cookie('cookie_consent'))) disabled @endif" _="on click put 'Vennligst vent...' into my.innerHTML then wait 20ms then add .disabled to me end">Start
                her for å
                beregne lønnsplassering</a>
        </div>
    @endif

    <div class="mb-5"></div>

    <div class="">
        <h3>Har du et utfylt lønnsplasseringsskjema?</h3>
        Du kan laste opp et utfylt lønnsskjema og arbeide videre med det her. Vi kan bare behandle Lønnsskjema Excel dokumenter som du finner under punktet <i>Last ned lønnsplasseringsskjema (Excel)</i> på <a href="{{ url('https://frikirken.no/arbeid#itemid-639') }}">Frikirkens websider</a>.<br /><br />
        Dokumentet blir ikke lagret, men vi tar ut informasjon om stillingen, din fødselsdato, tiltredelsesdatoen, kompetansen og ansienniteten.
        <form action="{{ is_null(request()->cookie('cookie_consent')) ? '#' : route('loadExcel') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <div class="row my-3">
                <div class="col-auto py-2">
                    <input type="file" name="excel_file" id="excelFile" required class="form-control">
                </div>
                <div class="col-auto text-start d-flex align-items-end py-2">
                    @if (session('applicationId'))
                        <button type="submit" class="btn btn-outline-primary @if (is_null(request()->cookie('cookie_consent'))) disabled @endif" _="on click if #uploadForm.checkValidity() then put 'Vennligst vent...' into my.innerHTML then wait 20ms then add @@disabled to me end">
                            Last opp og bruk dette skjemaet <br /><small>(avslutter skjemaet som du allerede holder på med)</small>
                        </button>
                    @else
                        <button type="submit" class="btn btn-primary @if (is_null(request()->cookie('cookie_consent'))) disabled @endif" _="on click if #uploadForm.checkValidity() then put 'Vennligst vent...' into my.innerHTML then wait 20ms then add @@disabled to me end">
                            Last opp og fortsett utfylling
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
@endsection

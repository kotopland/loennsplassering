@extends('layouts.app')

@section('content')
<div class="container-fluid py-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Systemdokumentasjon</li>
                </ol>
            </nav>
            <h1 class="h2 m-0 text-primary">Systemdokumentasjon: Frikirkens Lønnsberegner</h1>
        </div>
        <div>
            <a href="{{ route('admin.readme.show') }}" class="btn btn-outline-secondary btn-sm me-2">README.md</a>
            <a href="{{ route('admin.index') }}" class="btn btn-outline-primary btn-sm">Tilbake til Admin</a>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm sticky-top" style="top: 20px; z-index: 10;">
                <div class="card-header bg-primary text-white fw-bold">
                    Innholdsfortegnelse
                </div>
                <div class="list-group list-group-flush small">
                    <a href="#oversikt" class="list-group-item list-group-item-action">1. Oversikt og Formål</a>
                    <a href="#arkitektur" class="list-group-item list-group-item-action">2. Teknisk Arkitektur & Stakk</a>
                    <a href="#beregningslogikk" class="list-group-item list-group-item-action">3. Beregningslogikk for Lønn</a>
                    <a href="#soknadsprosess" class="list-group-item list-group-item-action">4. Søknadsprosess & Livssyklus</a>
                    <a href="#admin-moduler" class="list-group-item list-group-item-action">5. Administrasjonsmoduler</a>
                    <a href="#epost-arbeidsgiver" class="list-group-item list-group-item-action">6. E-post til Arbeidsgiver</a>
                    <a href="#cron-jobs" class="list-group-item list-group-item-action">7. Bakgrunnsjobber & Cron</a>
                    <a href="#sikkerhet-personvern" class="list-group-item list-group-item-action">8. Sikkerhet og Personvern</a>
                    <a href="#feilsoking" class="list-group-item list-group-item-action">9. Feilsøking og Vedlikehold</a>
                </div>
            </div>
        </div>

        <!-- Main Documentation Content -->
        <div class="col-lg-9 col-md-8">

            <!-- 1. Oversikt og Formål -->
            <div class="card shadow-sm mb-4" id="oversikt">
                <div class="card-header bg-light fw-bold text-primary">
                    1. Oversikt og Formål
                </div>
                <div class="card-body">
                    <p>
                        <strong>Frikirkens Lønnsberegner</strong> (<em>lonnsberegner.frikirken.no</em>) er et webbasert beregnings- og saksbehandlingssystem for Den Evangelisk Lutherske Frikirke. Systemet estimerer og genererer lønnsplassering for ansatte i menigheter, FriBU og Frikirkens fellesarbeid/hovedkontor i tråd med synodestyrets gjeldende lønnsavtale og lønnstabeller.
                    </p>
                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light h-100">
                                <h6 class="fw-bold text-primary">For Kandidater / Ansatte</h6>
                                <p class="small text-muted mb-0">Stegvis registrering av utdanning, arbeidserfaring og kurs. Forhåndsvisning av beregnet lønnstrinn og innsending til hovedkontoret.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light h-100">
                                <h6 class="fw-bold text-primary">For Arbeidsgivere / Menigheter</h6>
                                <p class="small text-muted mb-0">Mottak av standardisert e-postvarsel med stige, tillegg, ansiennitetsdato og instruksjoner om oppfølging i SDWorks og personalarkiv.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light h-100">
                                <h6 class="fw-bold text-primary">For Administratorer</h6>
                                <p class="small text-muted mb-0">Full saksbehandlingsoversikt, statusstyring, låsing/opplåsing, Excel-nedlasting og konfigurering av stiger, stillinger og maler.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Teknisk Arkitektur & Stakk -->
            <div class="card shadow-sm mb-4" id="arkitektur">
                <div class="card-header bg-light fw-bold text-primary">
                    2. Teknisk Arkitektur & Stakk
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered mb-3">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">Komponent</th>
                                <th style="width: 35%;">Teknologi / Bibliotek</th>
                                <th>Beskrivelse / Funksjon</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Backend Rammeverk</strong></td>
                                <td>Laravel 12 / 13 (PHP 8.3+)</td>
                                <td>MVC-arkitektur, Eloquent ORM, ruting, mellomvare og validering.</td>
                            </tr>
                            <tr>
                                <td><strong>Database</strong></td>
                                <td>MySQL / MariaDB</td>
                                <td>UUID som primærnøkler for skjemaer (<code>employee_cvs</code>), JSON-felter for fleksibel utdannings- og erfaringsstruktur.</td>
                            </tr>
                            <tr>
                                <td><strong>Frontend</strong></td>
                                <td>Blade, Bootstrap 5, SCSS, HTMX, _hyperscript</td>
                                <td>Responsivt brukergrensesnitt, dynamisk tidslinje, modalhåndtering.</td>
                            </tr>
                            <tr>
                                <td><strong>Excel-behandling</strong></td>
                                <td>PhpSpreadsheet & Maatwebsite Excel</td>
                                <td>Beregning og skriving til maler (<code>14lonnsskjema.xlsx</code>, expanded-varianter).</td>
                            </tr>
                            <tr>
                                <td><strong>E-post & Kø</strong></td>
                                <td>Symfony Mailer, SMTP / Mailgun / Spatie Preview</td>
                                <td>Asynkrone jobber for generering og e-postutsendelse (<code>database</code> queue).</td>
                            </tr>
                            <tr>
                                <td><strong>Sikkerhet & Botvern</strong></td>
                                <td>Google reCAPTCHA v3 & Signed Login Links</td>
                                <td>Passordfri magisk innlogging for admin og botbeskyttelse på skjema.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Beregningslogikk for Lønn -->
            <div class="card shadow-sm mb-4" id="beregningslogikk">
                <div class="card-header bg-light fw-bold text-primary">
                    3. Beregningslogikk for Lønnsplassering (SalaryEstimationService)
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        Kjerneberegningen i systemet utføres av <code>App\Services\SalaryEstimationService</code>. Beregningen følger Frikirkens offisielle tariffregler gjennom følgende steg:
                    </p>

                    <div class="accordion" id="calcAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                    <strong>A. 18-årsgrense og datojustering</strong>
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#calcAccordion">
                                <div class="accordion-body small">
                                    <ul>
                                        <li>All utdanning og arbeidserfaring før fylte 18 år filtreres bort (unntatt VGS/fagskole som registreres som bakgrunn).</li>
                                        <li>Perioder som strekker seg over 18-årsdagen justeres slik at startdato settes til 18-årsdagen (eller dagen etter for arbeid).</li>
                                        <li>Arbeidserfaring stoppes dagen før tiltredelsesdato (<code>work_start_date</code>).</li>
                                        <li>Sluttdatoer satt til den 1. i en måned justeres til siste dag i forrige måned for korrekt månedstelling.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                    <strong>B. Kompetansepoeng for Utdanning & Tak (Cap)</strong>
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#calcAccordion">
                                <div class="accordion-body small">
                                    <p>Kompetansepoeng tildeles basert på gradsnivå, studiepoeng og relevans:</p>
                                    <ul>
                                        <li><strong>Cand. Theol.:</strong> 7 poeng (Stige A, B, E, F) / 4 poeng (Stige D).</li>
                                        <li><strong>Mastergrad (&ge; 300 studiepoeng):</strong> 7 poeng ved full relevans / 6 poeng ellers.</li>
                                        <li><strong>Bachelorgrad (&ge; 180 studiepoeng):</strong> 3 poeng ved full relevans / 1 poeng ellers.</li>
                                        <li><strong>Årsenhet (&ge; 60 studiepoeng, relevant):</strong> 1 poeng.</li>
                                        <li><strong>Maksimumstak per stillingsstige:</strong>
                                            <ul>
                                                <li>Stige A, B, E, F: <strong>Maks 7 poeng</strong></li>
                                                <li>Stige C, gruppe 2: <strong>Maks 5 poeng</strong> / gruppe 1: <strong>Maks 2 poeng</strong></li>
                                                <li>Stige D: <strong>Maks 4 poeng</strong></li>
                                            </ul>
                                        </li>
                                        <li><strong>Konvertering til ansiennitet:</strong> Utdanning som gir 0 kompetansepoeng eller som overskrider taket, flyttes automatisk over til arbeidserfaring og gir uttelling i ansiennitet!</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                    <strong>C. Overlappshåndtering og Frikirkeregelen (100% stilling)</strong>
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#calcAccordion">
                                <div class="accordion-body small">
                                    <ul>
                                        <li><strong>Frikirkestillinger etter 1. mai 2014:</strong> Får automatisk 100% stillingsprosent og markeres som fullt relevant ansiennitet.</li>
                                        <li><strong>Maks 100% per tidsrom:</strong> Samlet arbeidsprosent for overlappende stillinger kan aldri overstige 100%. Systemet splitter tidsintervaller og capper prosenten med prioritet basert på startdato.</li>
                                        <li><strong>Overlapp arbeid og utdanning:</strong> Arbeid i perioder med kompetansegivende fulltidsutdanning splittes slik at man ikke får dobbelt uttelling, med mindre tariffavtalens unntaksregler for deltidsarbeid etter 2015 inntreffer.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                    <strong>D. Ansiennitetsdato, Lønnstrinn & Neste Opprykk</strong>
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#calcAccordion">
                                <div class="accordion-body small">
                                    <ul>
                                        <li><strong>Total ansiennitet:</strong> Summeres i måneder (ikke-relevant erfaring vektes med 50%).</li>
                                        <li><strong>Ansiennitet beregnet fra:</strong> Tiltredelsesdato fratrukket totalt antall opptjente ansiennitetsmåneder.</li>
                                        <li><strong>Lønnstrinn:</strong> Antall hele år fra ansiennitetsdato til dags dato (eller tiltredelse) pluss tildelte kompetansepoeng.</li>
                                        <li><strong>Neste ansiennitetsopprykk:</strong> Neste årlige årsdag for ansiennitetsdatoen.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Søknadsprosess & Livssyklus -->
            <div class="card shadow-sm mb-4" id="soknadsprosess">
                <div class="card-header bg-light fw-bold text-primary">
                    4. Søknadsprosess & Livssyklus for et Skjema
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3 text-center small">
                        <div class="col">
                            <div class="border rounded p-2 bg-light">
                                <span class="badge bg-primary mb-1">Steg 1</span><br>
                                <strong>Stilling & Tiltredelse</strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="border rounded p-2 bg-light">
                                <span class="badge bg-primary mb-1">Steg 2</span><br>
                                <strong>Utdanning</strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="border rounded p-2 bg-light">
                                <span class="badge bg-primary mb-1">Steg 3</span><br>
                                <strong>Erfaring</strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="border rounded p-2 bg-light">
                                <span class="badge bg-primary mb-1">Steg 4</span><br>
                                <strong>Kurs/Aktiviteter</strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="border rounded p-2 bg-light">
                                <span class="badge bg-success mb-1">Steg 5</span><br>
                                <strong>Beregning & Personalia</strong>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mt-3">Skjematilstander (Status & Behandlingsstatus):</h6>
                    <table class="table table-sm table-bordered small">
                        <thead class="table-light">
                            <tr>
                                <th>Felt</th>
                                <th>Verdi</th>
                                <th>Betydning / Effekt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td rowspan="3"><strong>processing_status</strong><br>(Behandlingsstatus)</td>
                                <td><span class="badge bg-secondary">innsendt</span></td>
                                <td>Skjemaet er levert av kandidaten og venter på at administrator starter vurdering.</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning text-dark">behandles</span></td>
                                <td>Administrator behandler søknaden. Skjemaet <em>låses automatisk for kandidatredigering</em> (status settes til <code>generated</code>).</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">godkjent</span></td>
                                <td>Lønnsplasseringen er ferdigbehandlet og godkjent. Skjemaet forblir låst for kandidaten.</td>
                            </tr>
                            <tr>
                                <td rowspan="3"><strong>status</strong><br>(Teknisk låsestatus)</td>
                                <td><code>null / modified</code></td>
                                <td>Skjemaet er åpent for redigering av kandidaten.</td>
                            </tr>
                            <tr>
                                <td><code>submitted</code></td>
                                <td>Kandidaten har trykket "Send inn for behandling".</td>
                            </tr>
                            <tr>
                                <td><code>generated</code></td>
                                <td>Lønnsskjema Excel er generert. Skrivebeskyttet for kandidaten (kan kun åpnes i lesemodus).</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 5. Administrasjonsmoduler -->
            <div class="card shadow-sm mb-4" id="admin-moduler">
                <div class="card-header bg-light fw-bold text-primary">
                    5. Administrasjonsmoduler
                </div>
                <div class="card-body">
                    <div class="list-group small">
                        <div class="list-group-item">
                            <h6 class="mb-1 fw-bold text-primary">Lønnskjemaer (<code>/admin/employee-cv</code>)</h6>
                            <p class="mb-1 text-muted">
                                Hovedoversikt over alle innsendte og opprettede lønnsskjemaer. Inneholder kolonner for Stillingstittel, Navn, Arbeidssted, Fødselsdato, Ansettelse, Status, Sist åpnet, Behandlingsstatus (dropdown), og Valg (Se/endre, Last ned XLS, E-post til arbeidsgiver, Lås/Lås opp, Slett).
                            </p>
                        </div>
                        <div class="list-group-item">
                            <h6 class="mb-1 fw-bold text-primary">Stillinger (<code>/admin/positions</code>)</h6>
                            <p class="mb-1 text-muted">
                                Oversikt og opprettelse av stillinger knyttet til stillingsstiger (A, B, C, D, E, F) og grupper (1, 2) samt stillingsbeskrivelser.
                            </p>
                        </div>
                        <div class="list-group-item">
                            <h6 class="mb-1 fw-bold text-primary">Lønnsstiger (<code>/admin/salary-ladders</code>)</h6>
                            <p class="mb-1 text-muted">
                                Definisjon av lønnstrinn og tabeller for hver lønnsstige og gruppe.
                            </p>
                        </div>
                        <div class="list-group-item">
                            <h6 class="mb-1 fw-bold text-primary">Lønnskjema Maler (<code>/admin/excel-templates</code>)</h6>
                            <p class="mb-1 text-muted">
                                Administrasjon og opplasting av de offisielle Excel-malene: <code>14lonnsskjema.xlsx</code>, <code>14lonnsskjema-expanded.xlsx</code>, og <code>14lonnsskjema-extraexpanded.xlsx</code>.
                            </p>
                        </div>
                        <div class="list-group-item">
                            <h6 class="mb-1 fw-bold text-primary">Admin Brukere & E-post (<code>/admin/users</code> & <code>/admin/settings</code>)</h6>
                            <p class="mb-1 text-muted">
                                Håndtering av administratorer som har tilgang til systemet, samt konfigurering av rapport-/varslingsepost (<code>report_email</code>).
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. E-post til Arbeidsgiver -->
            <div class="card shadow-sm mb-4" id="epost-arbeidsgiver">
                <div class="card-header bg-light fw-bold text-primary">
                    6. Funksjon: E-post til Arbeidsgiver
                </div>
                <div class="card-body">
                    <p class="small">
                        Fra oversikten over lønnsskjemaer kan administrator trykke på knappen <strong>«E-post»</strong> for å generere et offisielt lønnsplasseringsbrev til arbeidsgiver/leder:
                    </p>
                    <div class="bg-light p-3 border rounded font-monospace small mb-3">
                        Stige: [Beregnet stige, f.eks. A 1]<br>
                        Kompetansetillegg: [Beregnet tillegg]<br>
                        Ansvarstillegg*: 0<br>
                        Lønnsplassering inkl tillegg: [Beregnet trinn]<br>
                        Ansiennitet fra: [Dato d.m.Y]<br>
                        Neste ansiennitetsopprykk: [Dato d.m.Y]<br><br>
                        Vi minner om at arbeidsgiver er ansvarlig for å:<br>
                        - sjekke at informasjonen er riktig i lønnsskjema og i mail...<br>
                        - sjekke at attester og vitnemål stemmer overens...<br>
                        - gjennomgå lønnsplasseringen med ansatt...<br>
                        - Legge inn lønnsinformasjonen i SDWorks...<br>
                        - følge med på lønnsavtalen...<br>
                        - oppbevare lønnsplassering i personalarkiv...<br>
                        - sende inn skjema ved ansettelse, endringer og opphør...
                    </div>
                    <ul class="small text-muted mb-0">
                        <li>Administrator kan redigere mottakers e-post, emne og selve meldingsteksten før sending.</li>
                        <li>Dersom Excel-fil er generert, kan den automatisk legges ved e-posten.</li>
                    </ul>
                </div>
            </div>

            <!-- 7. Bakgrunnsjobber & Cron -->
            <div class="card shadow-sm mb-4" id="cron-jobs">
                <div class="card-header bg-light fw-bold text-primary">
                    7. Bakgrunnsjobber & Planlagte Oppgaver (Scheduler)
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered small">
                        <thead class="table-light">
                            <tr>
                                <th>Kommando / Jobb</th>
                                <th>Frekvens</th>
                                <th>Hensikt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>GenerateExcelJob</code></td>
                                <td>Ved innsending</td>
                                <td>Kjører ExcelGenerationService, skriver til mal og lagrer filen i <code>storage/app/public</code>.</td>
                            </tr>
                            <tr>
                                <td><code>NotifyAdminOfSubmissionJob</code></td>
                                <td>Ved fullført Excel</td>
                                <td>Sender e-postvarsel til konfigurert <code>report_email</code> om ny generert søknad.</td>
                            </tr>
                            <tr>
                                <td><code>ProcessUserSubmissionJob</code></td>
                                <td>Ved fullført Excel</td>
                                <td>Sender kvittering til kandidaten (dersom skjemaet ble sendt inn av kandidat, ikke admin).</td>
                            </tr>
                            <tr>
                                <td><code>employee-cvs:delete-old-records</code></td>
                                <td>Daglig (00:00)</td>
                                <td>Sletter lønnsskjemaer som ikke har vært åpnet på over 1 år i tråd med personvernregler.</td>
                            </tr>
                            <tr>
                                <td><code>employee-cvs:delete-emtpy-records</code></td>
                                <td>Hver time</td>
                                <td>Rydder bort tomme, forlatte påbegynte økter eldre enn 2 timer.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 8. Sikkerhet og Personvern -->
            <div class="card shadow-sm mb-4" id="sikkerhet-personvern">
                <div class="card-header bg-light fw-bold text-primary">
                    8. Sikkerhet og Personvern
                </div>
                <div class="card-body small">
                    <ul>
                        <li><strong>Dataminimering:</strong> Før kandidaten trykker "Send inn for behandling", lagres kun anonymiserte data knyttet til økten (ingen navn/adresse).</li>
                        <li><strong>Autentisering:</strong> Administratorer logger inn via sikre, tidsbegrensede engangslenker (Magic Links) sendt til e-post.</li>
                        <li><strong>Skjematilgang for kandidater:</strong> Sikret med fødselsdato og postnummer ved åpning av eksisterende søknader.</li>
                        <li><strong>CSRF & reCAPTCHA:</strong> Alle skjemapostinger er beskyttet med CSRF-tokens og Google reCAPTCHA v3 score-evaluering.</li>
                    </ul>
                </div>
            </div>

            <!-- 9. Feilsøking og Vedlikehold -->
            <div class="card shadow-sm mb-4" id="feilsoking">
                <div class="card-header bg-light fw-bold text-primary">
                    9. Feilsøking og Vedlikehold
                </div>
                <div class="card-body small">
                    <h6 class="fw-bold">Viktige loggfiler:</h6>
                    <ul>
                        <li><code>storage/logs/laravel.log</code>: Generelle systemfeil og unntak.</li>
                        <li><code>storage/logs/info.log</code>: Hendelseslogg for filnedlastinger og e-postutsendelser.</li>
                    </ul>

                    <h6 class="fw-bold mt-3">Nyttige Artisan-kommandoer:</h6>
                    <pre class="bg-light p-2 border rounded"><code># Kjøre kø-arbeider i bakgrunnen
php artisan queue:work

# Kjøre planlagte oppgaver manuelt
php artisan schedule:run

# Kjøre automatiserte tester
php artisan test --compact

# Opprette testdata for admin og lønnsskjemaer i utviklingsmiljø
php artisan db:seed --class=TestDataSeeder</code></pre>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

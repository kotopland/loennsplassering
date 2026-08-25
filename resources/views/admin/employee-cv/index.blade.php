@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="m-0">Lønnskjemaer</h1>
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#adminHelpModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-question-circle me-1" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                <path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286m1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94"/>
            </svg>
            Hjelp og veiledning
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="alert alert-light border shadow-sm alert-dismissible fade show mb-4 d-flex justify-content-between align-items-center" role="alert">
        <div>
            Denne siden viser alle lønnskjemaer som er registrert i webappen.
            Trenger du hjelp med statuser eller valg?
            <a href="#" class="alert-link ms-1" data-bs-toggle="modal" data-bs-target="#adminHelpModal">Åpne veiledning</a>.
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div class="table-responsive">
        <table class="table table-sm small table-hover">
            <thead>
                <tr>
                    <th>Stilling tittel</th>
                    <th>Navn</th>
                    <th>Arbeidssted</th>
                    <th>Fødselsdato</th>
                    <th>Ansettelse</th>
                    <th>Status</th>
                    <th>Sist åpnet</th>
                    <th>Behandlingsstatus</th>
                    <th>Valg</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employeeCV->sortByDesc('updated_at') as $employee)
                    <tr class="py-4 align-middle">
                        <td title="{{ $employee->job_title }}">{{ Str::limit($employee->job_title, 30) }}</td>
                        <td>{{ $employee->personal_info['name'] ?? '' }}</td>
                        <td>{{ $employee->personal_info['employer_and_place'] ?? '' }}</td>
                        <td>{{ $employee->birth_date }}</td>
                        <td>{{ $employee->work_start_date }}</td>
                        <td>
                            @if ($employee->status === 'generated')
                                <span class="badge bg-secondary">generert (låst)</span>
                            @elseif ($employee->status === 'submitted')
                                <span class="badge bg-info text-dark">innsendt</span>
                            @elseif ($employee->status)
                                <span class="badge bg-light text-dark border">{{ $employee->status }}</span>
                            @else
                                <span class="badge bg-light text-muted border">åpen</span>
                            @endif
                        </td>
                        <td>{{ $employee->last_viewed }}</td>
                        <td>
                            <form action="{{ route('admin.employee-cv.update-processing-status', $employee->id) }}" method="POST" class="d-inline">
                                @csrf
                                <select name="processing_status" class="form-select form-select-sm {{ ($employee->processing_status ?? 'innsendt') === 'godkjent' ? 'border-success text-success fw-bold' : (($employee->processing_status ?? 'innsendt') === 'behandles' ? 'border-warning text-dark fw-bold' : 'border-secondary text-secondary') }}" onchange="this.form.submit()" style="min-width: 120px;">
                                    <option value="innsendt" {{ ($employee->processing_status ?? 'innsendt') === 'innsendt' ? 'selected' : '' }}>Innsendt</option>
                                    <option value="behandles" {{ ($employee->processing_status ?? 'innsendt') === 'behandles' ? 'selected' : '' }}>Behandles</option>
                                    <option value="godkjent" {{ ($employee->processing_status ?? 'innsendt') === 'godkjent' ? 'selected' : '' }}>Godkjent</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Actions for employee CV">
                                <form method="POST" action="{{ route('open-application', ['application' => $employee->id]) }}">
                                    @csrf
                                    <input type="hidden" name="birth_date" value="{{ @$employee->birth_date }}">
                                    <input type="hidden" name="postal_code" value="{{ @$employee->personal_info['postal_code'] }}">
                                    <button type="submit" class="btn btn-sm btn-primary text-nowrap">Se/endre skjema</button>
                                </form>
                                @if ($employee->generated_file_path !== null)
                                    <a class="btn btn-sm btn-outline-primary text-nowrap" href="{{ route('admin.employee-cv.download-file', ['application' => $employee->id]) }}">Last ned XLS</a>
                                @endif
                                <form action="{{ route('admin.employee-cv.toggle-status', $employee->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $employee->status === 'generated' ? 'secondary' : 'success' }} text-nowrap" onclick="return confirm('{{ $employee->status === 'generated' ? 'Er du sikker på at du vil låse opp for kandidaten? Du som admin kan alltid redigere lønnskjemaer. Trykker du OK vil kandidaten igjen kunne redigere lønnskjemaet som nå er låst.' : 'Er du sikker på at du vil låse den for kandidaten? Da vil ikke kandidaten kunne redigere lønnskjemaet, men bare se det.' }} ? ')">
                                        {{ $employee->status === 'generated' ? 'Lås opp for kandidat' : 'Lås' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.employee-cv.destroy', $employee->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Er du sikker på at du vil slette dette skjemaet?')" class="btn btn-sm btn-outline-danger text-nowrap">Slett</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Admin Help Modal -->
    <div class="modal fade" id="adminHelpModal" tabindex="-1" aria-labelledby="adminHelpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="adminHelpModalLabel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-info-circle me-2 text-primary" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                            <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                        </svg>
                        Veiledning for administrasjon av lønnsskjemaer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Lukk"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-bold text-primary mb-2">1. Behandlingsstatus</h6>
                    <p class="mb-2">Behandlingsstatusen angir hvor i saksbehandlingsløpet lønnsplasseringen befinner seg:</p>
                    <ul class="list-group mb-4">
                        <li class="list-group-item">
                            <span class="badge bg-secondary me-2">Innsendt</span>
                            <strong>Innsendt:</strong> Skjemaet er sendt inn av kandidaten eller opprettet, og venter på saksbehandling.
                        </li>
                        <li class="list-group-item">
                            <span class="badge bg-warning text-dark me-2">Behandles</span>
                            <strong>Behandles:</strong> Søknaden er under aktiv behandling hos Frikirkens administrasjon. Når denne statusen velges, <em>låses skjemaet automatisk for kandidaten</em>.
                        </li>
                        <li class="list-group-item">
                            <span class="badge bg-success me-2">Godkjent</span>
                            <strong>Godkjent:</strong> Lønnsplasseringen er ferdigbehandlet og formelt godkjent/vedtatt. Skjemaet forblir låst for kandidaten.
                        </li>
                    </ul>

                    <h6 class="fw-bold text-primary mb-2">2. Status & Låsing for kandidat</h6>
                    <ul class="mb-4">
                        <li><strong>generert (låst):</strong> Skjemaet er låst for kandidatredigering, men kandidaten kan fortsatt åpne og se skjemaet som skrivebeskyttet. Excel-fil er generert og tilgjengelig for nedlasting.</li>
                        <li><strong>åpen:</strong> Kandidaten kan åpne og redigere opplysningene i skjemaet sitt via lenken de har mottatt.</li>
                        <li><em>Merk:</em> Du som <strong>administrator kan alltid se og endre</strong> alle lønnsskjemaer, uavhengig av om de er låst eller åpne for kandidaten.</li>
                    </ul>

                    <h6 class="fw-bold text-primary mb-2">3. Handlingsknapper (Valg)</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="border rounded p-2 h-100">
                                <span class="btn btn-sm btn-primary disabled mb-1">Se/endre skjema</span>
                                <p class="small text-muted mb-0">Åpner hele lønnsskjemaet i veiviseren. Her kan du justere stilling, utdanning, ansiennitet og personalia.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2 h-100">
                                <span class="btn btn-sm btn-outline-primary disabled mb-1">Last ned XLS</span>
                                <p class="small text-muted mb-0">Laster ned den ferdig utfylte Excel-filen med alle beregninger og lønnstrinn.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2 h-100">
                                <span class="btn btn-sm btn-outline-secondary disabled mb-1">Lås opp for kandidat / Lås</span>
                                <p class="small text-muted mb-0">Veksler kandidatens redigeringstilgang. "Lås opp" gir kandidaten mulighet til å korrigere skjemaet sitt.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2 h-100">
                                <span class="btn btn-sm btn-outline-danger disabled mb-1">Slett</span>
                                <p class="small text-muted mb-0">Sletter hele lønnsplasseringen og tilhørende data permanent fra systemet.</p>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-2">4. Hvordan oppdatere Excel-fil etter endringer?</h6>
                    <p class="small mb-0">
                        Dersom du gjør endringer i et skjema og vil generere en ny Excel-fil: Trykk <strong>Se/endre skjema</strong>, gå gjennom trinnene til siste steg, og trykk <strong>Send inn for behandling</strong>. Da overskrives Excel-filen med nye beregninger. Kandidaten mottar <em>ikke</em> e-post når admin gjør dette.
                    </p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Lukk veiledning</button>
                </div>
            </div>
        </div>
    </div>
@endsection

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
                    <th>Alder</th>
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
                        <td title="{{ $employee->job_title }}">{{ Str::limit($employee->job_title, 20) }}</td>
                        <td title="{{ $employee->personal_info['name'] }}">{{ Str::limit($employee->personal_info['name'], 20) }}</td>
                        <td title="{{ $employee->personal_info['employer_and_place'] }}">{{ Str::limit($employee->personal_info['employer_and_place'], 25) }}</td>
                        <td title="{{ $employee->birth_date }}">{{ $employee->age }}</td>
                        <td title="{{ $employee->work_start_date }}">{{ $employee->formatted_work_start_date }}</td>
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
                        <td title="{{ $employee->last_viewed?->format('Y-m-d H:i:s') }}">{{ $employee->last_viewed?->format('j M') }}</td>
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
                                <button type="button" class="btn btn-sm btn-outline-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#employerEmailModal-{{ $employee->id }}" title="Send e-post med lønnsplassering til arbeidsgiver">
                                    E-post
                                </button>
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

    <!-- Employer Email Modals -->
    @foreach ($employeeCV as $employee)
        <div class="modal fade" id="employerEmailModal-{{ $employee->id }}" tabindex="-1" aria-labelledby="employerEmailModalLabel-{{ $employee->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('admin.employee-cv.send-employer-email', $employee->id) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-light">
                            <h5 class="modal-title" id="employerEmailModalLabel-{{ $employee->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-envelope-at me-2 text-primary" viewBox="0 0 16 16">
                                    <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l1.326-.795 5.64 3.47A1 1 0 0 1 14 13h-1.5a.5.5 0 0 0 0 1H14a2 2 0 0 0 2-1.99V4a2 2 0 0 0-2-2zm0 1h12a1 1 0 0 1 1 1v.79l-7 4.2-7-4.2V4a1 1 0 0 1 1-1m0 2.25 6.54 3.924a.5.5 0 0 0 .52 0L14 5.25V12a1 1 0 0 1-.034.254L8.534 8.79a.5.5 0 0 0-.534 0L2.534 12.254A1 1 0 0 1 2 12z"/>
                                    <path d="M14.247 14.269c1.01 0 1.587-.857 1.587-2.025v-.21C15.834 10.43 14.64 9 12.52 9h-.035C10.42 9 9 10.36 9 12.432v.214C9 14.82 10.438 16 12.358 16h.044c.594 0 1.01-.14 1.25-.262a.5.5 0 0 0 .235-.436v-.4a.5.5 0 0 0-.696-.462c-.22.106-.52.18-.83.18-.99 0-1.63-.617-1.63-1.613v-.178c.32.252.75.407 1.25.407 1.218 0 2.12-.907 2.12-2.235 0-1.378-.96-2.316-2.22-2.316-1.588 0-2.58 1.282-2.58 2.877v.248c0 1.637 1.096 2.767 2.58 2.767.388 0 .74-.08 1.01-.2zM12.48 11.1c.677 0 1.13.498 1.13 1.215 0 .736-.453 1.233-1.13 1.233-.67 0-1.13-.497-1.13-1.233 0-.717.46-1.215 1.13-1.215"/>
                                </svg>
                                Send lønnsplassering til arbeidsgiver: {{ $employee->personal_info['name'] ?? 'Kandidat' }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Lukk"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Mottakers e-postadresse (arbeidsgiver/leder):</label>
                                    <input type="email" name="recipient_email" class="form-control" value="{{ $employee->employer_email_data['recipient_email'] ?? '' }}" required>
                                    <div class="form-text">
                                        {{ $employee->personal_info['manager_name'] ?? 'Leder' }} ({{ $employee->personal_info['employer_and_place'] ?? '' }})
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Emne:</label>
                                    <input type="text" name="subject" class="form-control" value="{{ $employee->employer_email_data['subject'] ?? '' }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">E-postinnhold:</label>
                                    <textarea name="email_body" class="form-control font-monospace small" rows="16" required>{{ $employee->employer_email_data['body_text'] ?? '' }}</textarea>
                                </div>
                                @if ($employee->generated_file_path !== null)
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="attach_file" value="1" id="attachFile-{{ $employee->id }}" checked>
                                            <label class="form-check-label" for="attachFile-{{ $employee->id }}">
                                                Legg ved generert Excel-lønnsskjema som vedlegg (.xlsx)
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Avbryt</button>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send me-1" viewBox="0 0 16 16">
                                    <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z"/>
                                </svg>
                                Send e-post til arbeidsgiver
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

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
                                <span class="btn btn-sm btn-outline-info disabled mb-1">E-post</span>
                                <p class="small text-muted mb-0">Åpner en forhåndsvisning med automatisk beregnet stige, tillegg, ansiennitet og opprykk, klar til sending til arbeidsgiver.</p>
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

@extends('layouts.app')

@section('customwidthbsclass')
    col-12
@endsection
@section('content')
    <div class="container">
        <div class="progress" role="progressbar" aria-label="Success example" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar bg-success" style="width: 100%">100%</div>
        </div>

        <h2>
            Estimert Lønnsberegning
        </h2>

        @if (session()->has('message'))
            <p class="alert my-2 {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
        @endif
        <div class="my-2 py-3">
            <p>
                <strong>
                    En maskinell beregning av lønnsplasseringen viser lønnstrinn {{ $salaryPlacement + $application->competence_points }}.
                </strong>
            </p>
            <p>
                <strong>Merk:</strong> Arbeidsgiver og sekretær for lønnsutvalget vil vurdere din kompetanse og
                ansiennitet før
                endelig lønn fastsettes. Det du har valg som relevant vil nødvendigvis ikke arbeidsgiver vektlegge.
            </p>
        </div>
        {{-- <div class="border border-primary border-1 bg-info px-3 ">
            <h3>Motta lønnsskjema med beregning pr e-post!</h3>
            <p><strong>For å unngå å miste informasjonen du har fylt inn</strong>, anbefaler vi at du skriver inn e-postadressen
                din i feltet
                under. Da sender vi deg en lenke til dette skjemaet og et ferdig utfylt lønnsskjema med beregnet lønnsplassering
                som Excel-dokument. Din e-postadresse brukes kun til å sende deg denne informasjonen og blir ikke lagret.
            </p>
            <div class="my-4">
                <form action="{{ route('export-as-xls') }}" method="get" id="salary_form" _="on submit set #sendEmail.innerHTML to 'Behandler. Sjekk din epost om noen minutter...'">
                    @csrf
                    <div class="pe-4">
                        <label for="email" class="form-label">E-post adresse</label>
                        <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" required style="max-width: 300px">
                    </div>
                    <button type="submit" name="submit" id="sendEmail" value="" class="btn btn-primary my-2">
                        Send Lønnsskjema (Excel) med
                        beregning<br />
                    </button>
                    <div class="ms-1">
                        <small>(du mottar den iløpet av et par minutter)</small>
                    </div>
                </form>
            </div>
        </div> --}}
        <div class="border border-primary border-1 bg-info px-3 my-4">
            <h3>Se lønnsplasseringen her:</h3>
            <div class="my-4">

                <a href="#" class="btn btn-sm btn-success" _="on click remove .d-none from #beregning then wait 200ms then go to #beregning @if (!$application->email_sent) then confirm('Ikke glem å motta beregningen og lenken til dette skjemaet via e-post') @endif">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 32 32">
                        <path fill="currentColor" d="M11 19v2H5v-2h2v-5H5v-2h2v-1h2v8zm8 0h-4v-2h2c1.103 0 2-.897 2-2v-2c0-1.103-.897-2-2-2h-4v2h4v2h-2c-1.103 0-2 .897-2 2v4h6zm6-8h-4v2h4v2h-3v2h3v2h-4v2h4c1.103 0 2-.897 2-2v-6c0-1.103-.897-2-2-2M2 4v4h2V4h4V2H4a2 2 0 0 0-2 2m26-2h-4v2h4v4h2V4a2 2 0 0 0-2-2M4 28v-4H2v4a2 2 0 0 0 2 2h4v-2zm24-4v4h-4v2h4a2 2 0 0 0 2-2v-4z">
                        </path>
                    </svg>
                    Se beregnet Lønnsplassering
                </a>
            </div>
            <div class="my-4">
                <a href="#" class="btn btn-sm btn-success" _="on click remove .d-none from #tidslinje then wait 200ms then go to #tidslinje  @if (!$application->email_sent) then confirm('Ikke glem å motta beregningen og lenken til dette skjemaet via e-post') @endif">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 2048 2048">
                        <path fill="currentColor" d="M256 640h640v128H256zm1024 512h512v128h-512zm768-896v1408H0V256zm-127 128h-514v384h-127V384H768v128H640V384H128v1153h512V896h128v641h512v-129h127v129h514zM897 896h639v128H897z">
                        </path>
                    </svg> Se en tidslinje over karriere og etter beregninger
                </a>
            </div>
            <div class="my-4 text-end">
                <a href={{ route('enter-employment-information', $application) }} class="btn btn-outline-primary">
                    Gå tilbake og gjør endringer i skjemaet
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitToOfficeModal">
                    Send inn til Frikirkens hovedkontor for behandling
                </button>
            </div>

            <div class="my-4 text-end">

            </div>
            <!-- Modal -->
            <div class="modal fade" id="submitToOfficeModal" tabindex="-1" aria-labelledby="submitToOfficeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="submitToOfficeModalLabel">Send inn for behandling</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('submit-for-processing') }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p>Fyll ut informasjonen nedenfor for å sende inn skjemaet til Frikirkens hovedkontor for endelig behandling.</p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Navn:</label>
                                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $application->personal_info['name'] ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mobile" class="form-label">Mobil:</label>
                                        <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mobile', $application->personal_info['mobile'] ?? '') }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="address" class="form-label">Adresse:</label>
                                        <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $application->personal_info['address'] ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <label for="postal_code" class="form-label">Postnr.:</label>
                                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code', $application->personal_info['postal_code'] ?? '') }}" required>
                                            </div>
                                            <div class="col-md-7">
                                                <label for="postal_place" class="form-label">Sted:</label>
                                                <input type="text" class="form-control" id="postal_place" name="postal_place" value="{{ old('postal_place', $application->personal_info['postal_place'] ?? '') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="personal_email" class="form-label">E-post:</label>
                                        <input type="email" class="form-control" id="personal_email" name="email" value="{{ old('email', $application->personal_info['email'] ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="employer_and_place" class="form-label">Arbeidsgiver/-sted:</label>
                                        <input type="text" class="form-control" id="employer_and_place" name="employer_and_place" value="{{ old('employer_and_place', $application->personal_info['employer_and_place'] ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="position_size" class="form-label">Stillingsstørrelse:</label>
                                        <input type="number" class="form-control" id="position_size" name="position_size" value="{{ old('position_size', $application->personal_info['position_size'] ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="bank_account" class="form-label">Bankkontonummer:</label>
                                        <input type="text" class="form-control" id="bank_account" name="bank_account" value="{{ old('bank_account', $application->personal_info['bank_account'] ?? '') }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Nærmeste overordnede:</label>
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" name="manager_name" placeholder="Navn" value="{{ old('manager_name', $application->personal_info['manager_name'] ?? '') }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" name="manager_mobile" placeholder="Mobil" value="{{ old('manager_mobile', $application->personal_info['manager_mobile'] ?? '') }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="email" class="form-control" name="manager_email" placeholder="E-post" value="{{ old('manager_email', $application->personal_info['manager_email'] ?? '') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Kontaktperson menighet:</label>
                                        <div class="row g-2">
                                            <div class="col-md-4"><input type="text" class="form-control" name="congregation_name" placeholder="Navn" value="{{ old('congregation_name', $application->personal_info['congregation_name'] ?? '') }}" required></div>
                                            <div class="col-md-4"><input type="text" class="form-control" name="congregation_mobile" placeholder="Mobil" value="{{ old('congregation_mobile', $application->personal_info['congregation_mobile'] ?? '') }}" required></div>
                                            <div class="col-md-4"><input type="email" class="form-control" name="congregation_email" placeholder="E-post" value="{{ old('congregation_email', $application->personal_info['congregation_email'] ?? '') }}" required></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                @if (auth()->check())
                                    <button type="submit" class="btn btn-primary">Generer skjemaet som sendes på epost bare til {{ \App\Models\Setting::where('key', 'report_email')->first()?->report_email ?? 'EMAIL ADDRESS MISSING IN SETTINGS' }} og ikke til kandidaten</button>
                                @else
                                    <button type="submit" class="btn btn-primary">Send til behandling</button>
                                @endif
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Avbryt og gå tilbake</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
        <div class="text-center pb-1">

            <br />

        </div>
        <div class="callout callout-primary d-none" id="beregning">
            <h3>Her er din beregnede lønnsplassering.</h3>Vær oppmerksom på at det kan være en usikkerhetsmargin på noen lønnstrinn når kompetanse og ansiennitet vurderes.
            <br />
            <br />
            Siden faktorer som utdanning, relevant arbeidserfaring og frivillig innsats er komplekse, kan det oppstå avvik i beregningen. Den største usikkerheten ligger i hvor relevant arbeidsgiver anser din utdanning og erfaring for stillingen. Denne relevansen kan påvirke om du plasseres et eller to lønnstrinn høyere eller lavere, for eksempel basert på din bachelor- eller mastergrad.
            <div class="mt-3 m-2">
                <strong>Sum måneder ansiennitet:</strong> {{ round($calculatedTotalWorkExperienceMonths, 1) }}<br />
            </div>
            <div class="m-2">
                <strong>Ansiennitet beregnet fra:</strong> {{ $ansiennitetFromDate }}<br />
            </div>
            <div class="m-2">
                <strong>Ansettelese fra:</strong> {{ $application->work_start_date }}<br />
            </div>
            <div class="m-2">
                <strong>Lønnsstige:</strong> {{ $ladder }}<br />
            </div>
            @if (!in_array($ladder, ['B', 'D']))
                <div class="m-2">
                    <strong>Lønnsgruppe:</strong> {{ $group }}<br />
                </div>
            @endif
            <div class="m-2">
                <strong>Lønnsplassering før gitt kompetansepoeng:</strong> {{ $salaryPlacement }}<br />
            </div>
            <div class="m-2">
                <strong>Kompetansepoeng:</strong> {{ $application->competence_points }}<br />
            </div>
            <div class="m-2">
                <strong>Lønnsplassering med kompetansepoeng:</strong> {{ $salaryPlacement + $application->competence_points }}<br />
            </div>
        </div>

        <div class="callout callout-primary d-none" id="tidslinje">
            @if (count($timeline) === 0)
                <div class="text-center">Tidslinje kan ikke lages da det ikke er noe kompetanse og arbeidserfaring</div>
            @else
                <h2>Tidslinje over din utdannelse og ansiennitet</h2>
                <div class="table-container">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="bg-primary text-white" colspan=" {{ count($timeline_adjusted) + 1 }}">
                                    <h4>Hva du har registrert</h4>
                                    <div class="badge bg-warning text-dark">Scroll horisontalt for å se full tidslinje</div>
                                </th>
                            </tr>
                        </thead>

                        <thead>
                            <tr>
                                <th>Utdannelse:</th>
                                @foreach ($timeline as $month)
                                    <th class="table-primary border-dark border-bottom border-start-0">{{ $month }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tableData as $key => $item)
                                @if ($key > 0)
                                    @if ($tableData[$key - 1]['type'] === 'education' && $item['type'] === 'work')
                                        <tr>
                                            <td>
                                                <strong>Arbeidserfaring:</strong>
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                                <tr>
                                    <td>{{ strlen($item['title']) > 30 ? substr($item['title'], 0, 30) . '...' : $item['title'] }}</td>
                                    @foreach ($timeline as $month)
                                        @php
                                            $itemStart = strtotime($item['start_date']);
                                            $itemEnd = strtotime($item['end_date']);
                                            $currentMonth = strtotime($month);
                                        @endphp
                                        <td class="table-primary">
                                            @if ($currentMonth >= $itemStart && $currentMonth <= $itemEnd)
                                                <span class="badge bg-primary" @class([
                                                    'education' => $item['type'] === 'education',
                                                    'work' => $item['type'] === 'work',
                                                ])>
                                                    {{ $item['percentage'] }}%
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <thead>
                            <tr>
                                <td><strong>Sum over total studie/ansiennitet</strong></td>
                                @foreach ($timeline as $month)
                                    @php
                                        $monthlySum = 0;
                                    @endphp

                                    @foreach ($tableData as $item)
                                        @php
                                            $itemStart = strtotime($item['start_date']);
                                            $itemEnd = strtotime($item['end_date']);
                                            $currentMonth = strtotime($month);
                                            if ($currentMonth >= $itemStart && $currentMonth <= $itemEnd) {
                                                $monthlySum += $item['percentage'];
                                            }
                                        @endphp
                                    @endforeach
                                    <td @class([
                                        'bg-danger' => $monthlySum > 100,
                                        'bg-warning' => $monthlySum === 0,
                                        'table-secondary',
                                    ])>
                                        <strong>{{ $monthlySum }}%</strong>
                                    </td>
                                @endforeach
                            </tr>
                        </thead>
                        <thead>
                            <tr>
                                <th></th>
                            </tr>

                            <tr class="border-top border-bottom-0 border-start-0 border-end-0 border-primary border-4">
                                <th class="bg-primary text-white" colspan=" {{ count($timeline_adjusted) + 1 }}">
                                    <h4>Maskinbehandlet</h4>
                                    <div class="badge bg-warning text-dark">Scroll horisontalt for å se full tidslinje</div>
                                </th>
                            </tr>
                        </thead>

                        <thead>
                            <tr>
                                <th>Utdannelse<br />(Beregnet til {{ $application->competence_points }} kompetansepoeng):</th>
                                @php
                                    $monthDifference = \Carbon\Carbon::parse($timeline[0])->diffInMonths(\Carbon\Carbon::parse($timeline_adjusted[0]));
                                @endphp
                                @for ($i = 0; $i < $monthDifference; $i++)
                                    <th class="table-primary border-dark border-bottom border-start-0">{{ $timeline[$i] }}</th>
                                @endfor
                                @foreach ($timeline_adjusted as $month)
                                    <th class="table-primary border-dark border-bottom border-start-0">{{ $month }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($tableData_adjusted as $key => $item)
                                @if ($key > 0)
                                    @if ($tableData_adjusted[$key - 1]['type'] === 'education' && $item['type'] === 'work')
                                        <tr>
                                            <td>
                                                <strong>Arbeidserfaring<br />(Som kan gi {{ $calculatedTotalWorkExperienceMonths }} mnd
                                                    ansiennitet):</strong>
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                                <tr>
                                    <td>{{ strlen($item['title']) > 30 ? substr($item['title'], 0, 30) . '...' : $item['title'] }}
                                        @if (@$item['comments'])
                                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ $item['comments'] }}">
                                                info
                                            </button>
                                        @endif
                                    </td>
                                    @for ($i = 1; $i <= $monthDifference; $i++)
                                        <td class="table-primary">
                                        </td>
                                    @endfor
                                    @foreach ($timeline_adjusted as $month)
                                        @php
                                            $itemStart = strtotime($item['start_date']);
                                            $itemEnd = strtotime($item['end_date']);
                                            $currentMonth = strtotime($month);
                                        @endphp
                                        <td class="table-primary">
                                            @if ($currentMonth >= $itemStart && $currentMonth <= $itemEnd)
                                                <span class="badge bg-primary" @class([
                                                    'education' => $item['type'] === 'education',
                                                    'work' => $item['type'] === 'work',
                                                ])>
                                                    {{ $item['percentage'] }}%
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <thead>
                            <tr>
                                <td><strong>Sum over total studie/ansiennitet</strong></td>
                                @foreach ($timeline as $month)
                                    @php
                                        $monthlySum = 0;
                                    @endphp
                                    @foreach ($tableData_adjusted as $item)
                                        @php
                                            $itemStart = strtotime($item['start_date']);
                                            $itemEnd = strtotime($item['end_date']);
                                            $currentMonth = strtotime($month);

                                            if ($currentMonth >= $itemStart && $currentMonth <= $itemEnd) {
                                                $monthlySum += $item['percentage'];
                                            }
                                        @endphp
                                    @endforeach
                                    <td @class([
                                        'bg-danger' => $monthlySum > 100,
                                        'bg-warning' => $monthlySum === 0,
                                        'table-secondary',
                                    ])>
                                        <strong>{{ $monthlySum }}%</strong>
                                    </td>
                                @endforeach
                            </tr>
                        </thead>
                    </table>
                </div>
            @endif
        </div>
    </div>
    {{-- @dd($adjustedDataset); --}}
@endsection

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
        <div class="border border-primary border-1 bg-info px-3 ">
            <h3>Motta lønnsskjema med beregning pr e-post!</h3>
            <p><strong>For å unngå å miste informasjonen du har fylt inn</strong>, anbefaler vi at du skriver inn e-postadressen
                din i feltet
                under. Da sender vi deg en lenke til dette skjemaet og et ferdig utfylt lønnsskjema med beregnet lønnsplassering
                som Excel-dokument. Din e-postadresse brukes kun til å sende deg denne informasjonen og blir ikke lagret.
            </p>
            <div class="my-4">
                <form action="{{ route('export-as-xls') }}" method="get" id="salary_form" _="on submit set #sendEmail.innerHTML to 'Behandler. Sjekk din epost om noen mulutter...'">
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
                        <small>(du mottar den iløpet av 2-15 minutter)</small>
                    </div>
                </form>
            </div>
        </div>
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
            </div>
        </div>
        <div class="text-center pb-1">

            <br />

        </div>
        <div class="callout callout-primary d-none" id="beregning">
            <h3>Her er din beregnede lønnsplassering.</h3> at det er en feilmargin på pluss minus 1-2 lønnstrinn. Siden
            utdanning, arbeidserfaring og også frivillig innsats er komplisert kan det ha blitt beregnet feil. Usikkerhetsmonn
            er hva av utdanning og arbeid som arbeidsgiver mener er relevant. Relevanse kan bety en eller to ekstra eller mindre
            på f.eks en bachelor eller master.
            <div class="m-2">
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
        {{-- @dd($adjustedDataset); --}}
    @endsection

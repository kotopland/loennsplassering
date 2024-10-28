@extends('layouts.app')

@section('content')
    <h1>Lønnsberegning</h1>
    @if (session()->has('message'))
        <p class="alert {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
    @endif
    <div class="my-4 border border-2 rounded rounded-2 p-3 bg-warning-subtle">
        <h3>Viktig!</h3>
        <p><strong>En maskinell beregning av lønnsplasseringen viser lønnstrinn {{ $salaryPlacement + $application->competence_points }}.</strong><br />
            <strong>Merk:</strong> Arbeidsgiver og sekretær for lønnsutvalget vil vurdere din kompetanse og ansiennitet før endelig lønn fastsettes. Det du har valg som relevant vil nødvendigvis ikke arbeidsgiver vektlegge.
        </p>
        <p>For å unngå å miste informasjonen du har fylt inn, anbefaler vi at du skriver inn e-postadressen din i feltet under. Da sender vi deg en lenke til dette skjemaet og et ferdig utfylt lønnsskjema med beregnet lønnsplassering som Excel-dokument. Din e-postadresse brukes kun til å sende deg denne informasjonen og blir ikke lagret.
        </p>
        <div class="m-2">
            <form action="{{ route('export-as-xls') }}" method="get" id="salary_form">
                @csrf
                <div class="pe-4">
                    <label for="email" class="form-label">E-post adresse</label>
                    <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" required>
                </div>
                <button type="submit" name="submit" value="" class="btn btn-success my-2">Send beregnet lønnsplassering på e-post<br/><small>(kan ta opptil 2 minutter)</small></button>
            </form>
        </div>
        Du bør også titte på:
        <li class="my-4">
            <a href="#" _="on click remove .d-none from #beregning then wait 200ms then go to #beregning @if (!$application->email_sent) then confirm('Ikke glem å motta beregningen og lenken til dette skjemaet via e-post') @endif">
                Nøkkeltall: Se beregnet Lønnsplassering
            </a>
        </li>
        <li class="mb-4">
            <a href="#" _="on click remove .d-none from #tidslinje then wait 200ms then go to #tidslinje  @if (!$application->email_sent) then confirm('Ikke glem å motta beregningen og lenken til dette skjemaet via e-post') @endif">
                Se en tidslinje over karriere og etter beregninger
            </a>
        </li>
        <li class="mb-4">
            <a href={{ route('enter-employment-information', $application) }} class="">
                Gå tilbake og gjør endringer i skjemaet
            </a>
        </li>
    </div>
    <div class="text-center pb-1">

        <br />

    </div>
    <div class="callout callout-primary d-none" id="beregning">
        <h3>Her er din beregnede lønnsplassering.</h3> at det er en feilmargin på pluss minus 1-2 lønnstrinn. Siden utdanning, arbeidserfaring og også frivillig innsats er komplisert kan det ha blitt beregnet feil. Usikkerhetsmonn er hva av utdanning og arbeid som arbeidsgiver mener er relevant. Relevanse kan bety en eller to ekstra eller mindre på f.eks en bachelor eller master.
        <div class="m-2">
            <strong>Sum måneder ansiennitet:</strong> {{ $calculatedTotalWorkExperienceMonths }}<br />
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
        <h2>Tidslinje over din utdannelse og ansiennitet</h2>
        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>
                            <h5>Hva du har registrert</h5>
                        </th>
                    </tr>
                </thead>

                <thead>
                    <tr>
                        <th>Utdannelse/Ansiennitets opplysninger:</th>
                        @foreach ($timeline as $month)
                            <th class="table-primary border-dark border-bottom border-start-0">{{ $month }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tableData as $item)
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
                    <tr class="">
                        <th>
                            <h5>Maskinbehandlet</h5>
                        </th>
                    </tr>
                </thead>

                <thead>
                    <tr>
                        <th>Utdannelse/Ansiennitets opplysninger:</th>
                        @php
                            $monthDifference = \Carbon\Carbon::parse($timeline[0])->diffInMonths(\Carbon\Carbon::parse($timeline_adjusted[0]));
                        @endphp
                        @for ($i = 0; $i <= $monthDifference; $i++)
                            <th class="table-primary border-dark border-bottom border-start-0">{{ $timeline[$i] }}</th>
                        @endfor
                        @foreach ($timeline_adjusted as $month)
                            <th class="table-primary border-dark border-bottom border-start-0">{{ $month }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>

                    @foreach ($tableData_adjusted as $item)
                        <tr>
                            <td>{{ strlen($item['title']) > 30 ? substr($item['title'], 0, 30) . '...' : $item['title'] }}</td>
                            @for ($i = 1; $i <= $monthDifference; $i++)
                                <td class="table-primary"></td>
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

        {{-- @dd($adjustedDataset); --}}
    @endsection

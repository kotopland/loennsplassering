@extends('layouts.app')

@section('content')
    <h1>Lønnsberegning</h1>
    @if (session()->has('message'))
        <p class="alert {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
    @endif
    <div class="my-4 border border-2 rounded rounded-2 p-3 bg-warning-subtle">
        <h3>Viktig!</h3>
        <p>Unngå å miste beregningen. Få lenke til skjemaet samt et ferdig utfylt og beregnet lønnsplassering. Det er viktig å bemerke at dette er ikke en endelig da arbeidsgiver vil sammen med sekretær for lønnsuvalget vurderinge hva som er relevant kompetanse og ansiennitet for stilingen din .</p>
        <p>Vi lagrer eller logger ikke e-post adressen og vi bruker den bare for å sende disse opplysningene pr e-post.</p>
        <div class="m-2">
            <form action="{{ route('export-as-xls') }}" method="get" id="salary_form">
                @csrf
                <div class="pe-4">
                    <label for="email" class="form-label">E-post adresse</label>
                    <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" required>
                </div>
                <button type="submit" name="submit" value="" class="btn btn-success my-2">Send beregnet lønnsplassering på e-post</button>
            </form>
        </div>
    </div>
    <div class="text-md-end text-center pb-1">
        <button class="btn btn-primary my-2" _="on click remove .d-none from #beregning then go to #beregning @if (!$application->email_sent) then confirm('Ikke glem å motta beregningen og lenken til dette skjemaet via e-post') @endif">
            Nøkkeltall: Lønnsplassering
        </button>
        <button class="btn btn-primary my-2" _="on click remove .d-none from #tidslinje then go to #tidslinje  @if (!$application->email_sent) then confirm('Ikke glem å motta beregningen og lenken til dette skjemaet via e-post') @endif">
            Tidslinje over karriere
        </button>
        <a href={{ route('enter-employment-information', $application) }} class="btn btn-secondary">
            Gjør endringer i skjemaet
        </a>

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
                            <h2>Hva du har registrert</h2>
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
                        <td><strong>Sum over total studie/ansiennitet (maks 100% gir uttelling)</strong></td>
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
                            <h2>Maskinbehandlet med regler fra lønnsavtalen</h2>
                        </th>
                    </tr>
                </thead>

                <thead>
                    <tr>
                        <th>Utdannelse/Ansiennitets opplysninger:</th>
                        @foreach ($timeline_adjusted as $month)
                            <th class="table-primary border-dark border-bottom border-start-0">{{ $month }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tableData_adjusted as $item)
                        <tr>
                            <td>{{ strlen($item['title']) > 30 ? substr($item['title'], 0, 30) . '...' : $item['title'] }}</td>
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
                        <td><strong>Sum over total studie/ansiennitet (maks 100% gir uttelling)</strong></td>
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

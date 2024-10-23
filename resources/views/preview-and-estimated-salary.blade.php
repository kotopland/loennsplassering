@extends('layouts.app')

@section('content')
    <h1>Beregning av lønnsplassering</h1>
    @if (session()->has('message'))
        <p class="alert {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
    @endif
    <div class="callout callout-primary bg-primary-subtle">
        Her er din beregnede lønnsplassering. Tenk at det er en feilmargin på pluss minus 1-2 lønnstrinn. Siden utdanning, arbeidserfaring og også frivillig innsats er komplisert kan det ha blitt beregnet feil. Usikkerhetsmonn er hva av utdanning og arbeid som arbeidsgiver mener er relevant. Relevanse kan bety en eller to ekstra eller mindre på f.eks en bachelor eller master.
    </div>
    <div class="m-2">
        <strong>Sum måneder ansiennitet:</strong> {{ $calculatedTotalWorkExperienceMonths }}<br />
    </div>
    <div class="m-2">
        <strong>Ansiennitet beregnet fra:</strong> {{ $ansiennitetFromDate }}<br />
    </div>
    <div class="m-2">
        <strong>Ansettelese fra:</strong> {{ $employeeCV->work_start_date }}<br />
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
        <strong>Kompetansepoeng:</strong> {{ $employeeCV->competence_points }}<br />
    </div>
    <div class="m-2">
        <strong>Lønnsplassering med kompetansepoeng:</strong> {{ $salaryPlacement + $employeeCV->competence_points }}<br />
    </div>
    <div class="m-2">
        <a href="{{ route('export-as-xls') }}" class="btn btn-success btn-lg">Last ned Utfylt lønnsplasseringsskjema i Excel</a>
    </div>

    <h2>Din tidslinje over utdannelse og ansiennitet</h2>
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
                        <td>{{ $item['title'] }}</td>
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
                        <td>{{ $item['title'] }}</td>
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
        {{-- @dd($adjustedDataset); --}}
    @endsection

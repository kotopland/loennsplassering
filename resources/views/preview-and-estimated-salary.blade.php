<!DOCTYPE html>
<html>

<head>
    <title>Lønnsplassering</title>
    <style>
        .table-container {
            width: 100% !important;
            overflow-x: auto !important;
        }

        table {
            width: max-content !important;
            border-collapse: collapse !important;
        }

        th,
        td {
            border: 1px solid black !important;
            padding: 8px !important;
            text-align: left !important;
            white-space: nowrap !important;
        }

        td:first-child {
            position: sticky !important;
            left: 0 !important;
            background-color: white !important;
        }
    </style>

    <script src="https://unpkg.com/hyperscript.org@0.9.11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
</head>

<body>
    <h1>Salary Preview</h1>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Registrert lønnskjema for plassering</th>
                </tr>
            </thead>

            <thead>
                <tr>
                    <th>Title</th>
                    @foreach ($timeline as $month)
                        <th>{{ $month }}</th>
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
                            <td>
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
                    <td><strong>Sum per month</strong></td>
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
                        ])>
                            <strong>{{ $monthlySum }}%</strong>
                        </td>
                    @endforeach
                </tr>
            </thead>
            <thead>
                <tr>
                    <th>Maskinelt endret lønnskjema for plassering</th>
                </tr>
            </thead>

            <thead>
                <tr>
                    <th>Title</th>
                    @foreach ($timeline_adjusted as $month)
                        <th>{{ $month }}</th>
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
                            <td>
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
                    <td><strong>Sum per month</strong></td>
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
                        ])>
                            <strong>{{ $monthlySum }}%</strong>
                        </td>
                    @endforeach
                </tr>
            </thead>
        </table>
    </div>
    Total work experience months: {{ $calculatedTotalWorkExperienceMonths }}<br />
    Ansiennitet fra: {{ $ansiennitetFromDate }}<br />
    Ansettes fra: {{ $employeeCV->work_start_date }}
    <a href="{{ route('export-as-xls') }}" class="btn btn-success btn-lg">Last ned Utfylt lønnsplasseringsskjema</a>
    {{-- @dd($adjustedDataset); --}}
</body>

</html>

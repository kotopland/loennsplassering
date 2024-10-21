<!DOCTYPE html>
<html>

<head>
    <title>Salary Preview</title>
    <style>
        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: max-content;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            white-space: nowrap;
        }

        td:first-child {
            position: sticky;
            left: 0;
            background-color: white;
        }
    </style>
</head>

<body>
    <h1>Salary Preview</h1>
    <div class="table-container">
        <table>
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
                                    <span class="badge" @class([
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
                                    <span class="badge" @class([
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
        </table>
    </div>
    Total work experience: {{ $calculatedTotalWorkExperienceMonths }}<br />
    Ansiennitet fra: {{ $ansiennitetFromDate }}<br />
    Ansettes fra: {{ $employeeCV->work_start_date }}
    <a href="{{ route('export-as-xls') }}">Last ned Utfylt lønnsplasseringsskjema</a>
    @dd($adjustedDataset);
</body>

</html>

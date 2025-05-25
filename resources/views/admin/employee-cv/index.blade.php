@extends('layouts.app')

@section('content')
    <h2>Stillinger</h2>
    <table class="table table-sm" style="max-width: 100%">
        <thead>
            <tr>
                <th>Stillingstittel</th>
                <th>Fødselsdato</th>
                <th>Ansettelse</th>
                <th>E-post sendt</th>
                <th>Sist åpnet</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employeeCV as $employee)
                <tr>
                    <td><a href="{{ route('open-application', ['application' => $employee->id]) }}">{{ $employee->job_title }}</a></td>
                    <td>{{ $employee->birth_date }}</td>
                    <td>{{ $employee->work_start_date }}</td>
                    <td>{{ $employee->email_sent }}</td>
                    <td>{{ $employee->last_viewed }}</td>
                    <td>
                        <form action="{{ route('admin.employee-cv.destroy', $employee->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Er du sikker på at du vil slette denne stillingen?')" class="btn btn-sm btn-danger mb-1">Slett</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

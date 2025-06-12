@extends('layouts.app')

@section('content')
    <h1>Stillinger</h1>
    <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
        Denne siden viser alle lønnskjemaer som er registrert i webappen. Trykker du på en av linkene til en stilling, vil du laste inn det skjemaet slik at du kan arbeide videre med den.
        Linken kan videresendes til en annen person dersom du ønsker at andre skal kunne arbeide på den.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
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

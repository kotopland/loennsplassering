@extends('layouts.app')

@section('content')
    <h1>Stillinger</h1>
    <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
        Denne siden viser alle lønnskjemaer som er registrert i webappen. Trykker du på en av linkene til en stilling, vil du laste inn det skjemaet slik at du kan arbeide videre med den.
        Linken kan videresendes til en annen person dersom du ønsker at andre skal kunne arbeide på den.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <table class="table table-sm " style="max-width: 100%">
        <thead>
            <tr>
                <th>Stillingstittel</th>
                <th>Fødselsdato</th>
                <th>Ansettelse</th>
                <th>E-post sendt</th>
                <th>Status</th>
                <th>Sist åpnet</th>
                <th>Valg</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employeeCV as $employee)
                <tr>
                    <td class="py-4">{{ $employee->job_title }}</td>
                    <td class="py-4">{{ $employee->birth_date }}</td>
                    <td class="py-4">{{ $employee->work_start_date }}</td>
                    <td class="py-4">{{ $employee->email_sent ? 'Ja' : 'Nei' }}</td>
                    <td class="py-4">{{ $employee->status }}</td>
                    <td class="py-4">{{ $employee->last_viewed }}</td>
                    <td class="py-4">
                        <a class="m-2 btn btn-sm btn-outline-primary" href="{{ route('open-application', ['application' => $employee->id]) }}">Endre Online</a>
                        @if ($employee->generated_file_path !== null)
                            <a class="m-2 btn btn-sm btn-outline-primary" href="{{ route('admin.employee-cv.download-file', ['application' => $employee->id]) }}">Last ned utfylt skjema</a>
                        @endif
                        @if ($employee->status !== null)
                            <form action="{{ route('admin.employee-cv.toggle-status', $employee->id) }}" method="POST" style="display: inline-block;" onclick="if (!confirm('Er du sikker? Du som admin kan alltid redigere lønnskjemaer. Trykker du OK vil kandidaten igjen kunne redigere lønnskjemaet som nå er låst.')) return false;">
                                @csrf
                                <button type="submit" class="m-2 btn btn-sm btn-warning mb-1">
                                    {{ $employee->status === 'generated' ? 'Gjør redigerbar for kandidaten' : 'Lås (generert)' }}
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('admin.employee-cv.destroy', $employee->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Er du sikker på at du vil slette denne stillingen?')" class="m-2 btn btn-sm btn-danger mb-1">Slett</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

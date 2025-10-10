@extends('layouts.app')

@section('content')
    <h1>Lønnskjemaer</h1>
    <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
        Denne siden viser alle lønnskjemaer som er registrert i webappen.
        <br />
        Status kan ha "generert" er lønnsskjemaer der brukeren eller deg har sendt til behandling. Da genereres en Excel fil med lønnsplassering og du kan laste den ned med "Last ned XLS". Når en fil er sendt inn til behandling vil skjemaet bli lesbart for kandidaten, men uten mulighet for å endre lønnsskjemaet. Du som admin kan endre skjemaet, og du trenger ikke å låse skjemaet opp. "Slett" sletter hele lønnsplasseringen.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <table class="table table-sm small">
        <thead>
            <tr>
                <th>Stillingstittel</th>
                <th>Fødselsdato</th>
                <th>Ansettelse</th>
                {{-- <th>E-post sendt</th> --}}
                <th>Status</th>
                <th>Sist åpnet</th>
                <th>Valg</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employeeCV->sortByDesc('updated_at') as $employee)
                <tr class="py-4 align-middle">
                    <td>{{ $employee->job_title }}</td>
                    <td>{{ $employee->birth_date }}</td>
                    <td>{{ $employee->work_start_date }}</td>
                    {{-- <td>{{ $employee->email_sent ? 'Ja' : 'Nei' }}</td> --}}
                    <td>{{ $employee->status }}</td>
                    <td>{{ $employee->last_viewed }}</td>
                    <td>
                        <div class="btn-group" role="group" aria-label="Actions for employee CV">
                            <form method="POST" action="{{ route('open-application', ['application' => $employee->id]) }}">
                                @csrf
                                <input type="hidden" name="birth_date" value="{{ @$employee->birth_date }}">
                                <input type="hidden" name="postal_code" value="{{ @$employee->personal_info['postal_code'] }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Endre skjema</button>
                            </form>
                            @if ($employee->generated_file_path !== null)
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.employee-cv.download-file', ['application' => $employee->id]) }}">Last ned XLS</a>
                            @endif
                            @if ($employee->status !== null)
                                <form action="{{ route('admin.employee-cv.toggle-status', $employee->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Er du sikker? Du som admin kan alltid redigere lønnskjemaer. Trykker du OK vil kandidaten igjen kunne redigere lønnskjemaet som nå er låst.')">
                                        {{ $employee->status === 'generated' ? 'Lås opp for kandidat' : 'Lås' }}
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('admin.employee-cv.destroy', $employee->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Er du sikker på at du vil slette dette skjemaet?')" class="btn btn-sm btn-danger">Slett</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

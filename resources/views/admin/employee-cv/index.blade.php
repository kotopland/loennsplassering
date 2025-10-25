@extends('layouts.app')

@section('content')
    <h1>Lønnskjemaer</h1>
    <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
        Denne siden viser alle lønnskjemaer som er registrert i webappen.

        <a href="#" _="on click remove .d-none from #page-info then remove me">@lang('Les mer...')</a> <span id="page-info" class="d-none">
            <span id="shortendIntroText">
                <br />
                Status kan ha "generert" er lønnsskjemaer der brukeren eller deg har sendt til behandling. Da genereres en Excel fil med lønnsplassering og du kan laste den ned med "Last ned XLS". Når en fil er sendt inn til behandling vil skjemaet bli lesbart for kandidaten, men uten mulighet for å endre lønnsskjemaet. Du som admin kan endre skjemaet uten å låse opp skjemaet. For å generere en ny Excel fil som du kan laste ned, må du endre skjemaet og fullføre til innsending. Kandidaten vil ikke motta epost slik at du kan kontrollere/endre Excel filen lokalt før endelig lønnsplassering. "Slett" sletter hele lønnsplasseringen.
            </span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <table class="table table-sm small">
        <thead>
            <tr>
                <th>Stillingstittel</th>
                <th>Navn</th>
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
                    <td>{{ $employee->personal_info['name'] ?? '' }}</td>
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
                                <button type="submit" class="btn btn-sm btn-primary">Se/endre skjema</button>
                            </form>
                            @if ($employee->generated_file_path !== null)
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.employee-cv.download-file', ['application' => $employee->id]) }}">Last ned XLS</a>
                            @endif
                            <form action="{{ route('admin.employee-cv.toggle-status', $employee->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-{{ $employee->status === 'generated' ? 'secondary' : 'success' }}" onclick="return confirm('{{ $employee->status === 'generated' ? 'Er du sikker på at du vil låse opp for kandidaten? Du som admin kan alltid redigere lønnskjemaer. Trykker du OK vil kandidaten igjen kunne redigere lønnskjemaet som nå er låst.' : 'Er du sikker på at du vil låse den for kandidaten? Da vil ikke kandidaten kunne redigere lønnskjemaet, men bare se det.' }} ? ')">
                                    {{ $employee->status === 'generated' ? 'Lås opp for kandidat' : 'Lås' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.employee-cv.destroy', $employee->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Er du sikker på at du vil slette dette skjemaet?')" class="btn btn-sm btn-outline-danger">Slett</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

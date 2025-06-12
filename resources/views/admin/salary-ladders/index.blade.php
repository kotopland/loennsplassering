@extends('layouts.app')

@section('content')
    <h1>Lønnstiger</h1>

    <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
        Denne siden administrerer lønnsstigene. Hver stige er ett år. Der tallene er like, betyr det at de gjelder for de årene.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <table class="table table-sm" style="max-width: 100%">
        <thead>
            <tr>
                <th>Stige</th>
                <th>Gruppe</th>
                <th>Lønnsstige</th>
                <th>Verktøy</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($salaryLadders as $salaryLadder)
                <tr>
                    <td>{{ $salaryLadder->ladder }}</td>
                    <td>{{ $salaryLadder->group }}</td>
                    <td>{{ implode(', ', $salaryLadder->salaries) }}</td>
                    <td>
                        <a href="{{ route('admin.salary-ladders.edit', $salaryLadder) }}" class="btn btn-sm btn-primary mb-1">Endre</a>
                        <form action="{{ route('admin.salary-ladders.destroy', $salaryLadder) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Dette er vanligvis ikke nødvendig!!!! Det er bedre å endre en stige. Er du sikker på at du vil slette denne stigen?')" class="btn btn-sm btn-danger mb-1">Slett</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mb-5">
        <a href="{{ route('admin.salary-ladders.create') }}" class="btn btn-primary">Opprett Lønnsstige</a>
    </div>
@endsection

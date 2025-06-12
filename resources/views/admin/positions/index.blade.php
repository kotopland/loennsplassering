@extends('layouts.app')

@section('content')
    <h1>Stillinger</h1>
    <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
        Denne siden viser alle mulige stillinger som er tilgjengelig i webappen. Disse vises i dropdown menyen sortert på tittel.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <div class="mb-5">
        <a href="{{ route('admin.positions.create') }}" class="btn btn-primary">Opprett Stilling</a>
    </div>
    <table class="table table-sm" style="max-width: 100%">
        <thead>
            <tr>
                <th>Tittel</th>
                <th>Stige</th>
                <th>Gruppe</th>
                <th>Beskrivelse</th>
                <th>Verktøy</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($positions as $position)
                <tr>
                    <td>{{ $position->name }}</td>
                    <td>{{ $position->ladder }}</td>
                    <td>{{ $position->group }}</td>
                    <td class="text-break">{{ $position->description }}</td>
                    <td>
                        <a href="{{ route('admin.positions.edit', $position) }}" class="btn btn-sm btn-primary mb-1">Endre</a>
                        <form action="{{ route('admin.positions.destroy', $position) }}" method="POST" style="display: inline-block;">
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

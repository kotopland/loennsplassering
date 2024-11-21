@extends('layouts.app')

@section('content')

<h2>Lønnstiger</h2>

<div class="mb-5">
    <a href="{{ route('admin.salary-ladders.create') }}" class="btn btn-primary">Opprett Lønnsstige</a>
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
                <a href="{{ route('admin.salary-ladders.edit', $salaryLadder) }}"
                    class="btn btn-sm btn-primary mb-1">Endre</a>
                <form action="{{ route('admin.salary-ladders.destroy', $salaryLadder) }}" method="POST"
                    style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Er du sikker på at du vil slette denne stigen?')"
                        class="btn btn-sm btn-danger mb-1">Slett</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
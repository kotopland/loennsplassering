@extends('layouts.app')

@section('content')
    <h1>Opprett Ny Stilling</h1>

    <form action="{{ route('admin.positions.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Tittle på stillingen:</label>
            <input type="text" class="form-control" name="name" id="name" required>
        </div>
        <div class="mb-3">
            <label for="ladder" class="form-label">Stige:</label>
            <input type="text" class="form-control" name="ladder" id="ladder" required>
        </div>
        <div class="mb-3">
            <label for="group" class="form-label">Gruppe:</label>
            <input type="number" class="form-control" name="group" id="group" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Beskrivelse:</label>
            <textarea class="form-control" name="description" id="description"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Opprett</button>
    </form>
@endsection

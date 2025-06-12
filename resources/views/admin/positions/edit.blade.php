@extends('layouts.app')

@section('content')
    <h1>Endre Stillinger</h1>

    <form action="{{ route('admin.positions.update', $position) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Tittel på stillingen:</label>
            <input type="text" class="form-control" name="name" id="name" value="{{ $position->name }}" required>
        </div>
        <div class="mb-3">
            <label for="ladder" class="form-label">Stige:</label>
            <input type="text" class="form-control" name="ladder" id="ladder" value="{{ $position->ladder }}" required>
        </div>
        <div class="mb-3">
            <label for="group" class="form-label">Gruppe:</label>
            <input type="number" class="form-control" name="group" id="group" value="{{ $position->group }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Beskrivelse:</label>
            <textarea class="form-control" name="description" id="description">{{ $position->description }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Oppdater</button>
    </form>
@endsection

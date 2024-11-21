@extends('layouts.app')

@section('content')
<h2>Opprett Lønnsstige</h2>

<form action="{{ route('admin.salary-ladders.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="ladder" class="form-label">Stige:</label>
        <input type="text" class="form-control" name="ladder" id="ladder" required>
    </div>
    <div class="mb-3">
        <label for="group" class="form-label">Gruppe:</label>
        <input type="number" class="form-control" name="group" id="group" required>
    </div>
    <div class="mb-3">
        <label for="salaries" class="form-label">Lønnsstige:</label>
        <textarea class="form-control" name="salaries" id="salaries" required
            oninput="this.value = this.value.replace(/[^0-9,]/g, '').replace(/,,+/g, ',');" pattern="^(\d+)(,\d+)*$"
            title="Only numbers separated by commas are allowed."></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Opprett</button>
</form>
@endsection
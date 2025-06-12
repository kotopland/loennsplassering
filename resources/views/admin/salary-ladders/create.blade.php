@extends('layouts.app')

@section('content')
    <h1>Opprett Lønnsstige</h1>
    <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
        En stige må være identisk med lønnskjemaet som er lastet opp.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <form action="{{ route('admin.salary-ladders.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="ladder" class="form-label">Stige: (A,B,C,D,E,F)</label>
            <input type="text" class="form-control" name="ladder" id="ladder" required>
        </div>
        <div class="mb-3">
            <label for="group" class="form-label">Gruppe: (1 eller 2)</label>
            <input type="number" class="form-control" name="group" id="group" required>
        </div>
        <div class="mb-3">
            <label for="salaries" class="form-label">Lønnsstige:</label>
            <textarea class="form-control" name="salaries" id="salaries" required oninput="this.value = this.value.replace(/[^0-9,]/g, '').replace(/,,+/g, ',');" pattern="^(\d+)(,\d+)*$" title="Only numbers separated by commas are allowed."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Opprett</button>
    </form>
@endsection

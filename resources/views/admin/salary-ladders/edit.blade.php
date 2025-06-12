@extends('layouts.app')

@section('content')
    <h1>Endre Lønnsstige</h1>
    <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
        Her justerer du lønnsstigen. Dersom du skal flytte stigen med ett år, bruker du "Flytt Stige" knappene. Dersom du må endre stigen, pass på at den er lik lønnsskjemaet.
        Når du justerer lønnsstigen må du laste opp ny excel mal!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <form action="{{ route('admin.salary-ladders.update', $salaryLadder) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="ladder" class="form-label">Stige: (A,B,C,D,E,F)</label>
            <input type="text" class="form-control" name="ladder" id="ladder" value="{{ $salaryLadder->ladder }}" required>
        </div>
        <div class="mb-3">
            <label for="group" class="form-label">Gruppe: (1 eller 2)</label>
            <input type="number" class="form-control" name="group" id="group" value="{{ $salaryLadder->group }}" required>
        </div>
        <div class="mb-3">
            <label for="salaries" class="form-label">Lønnsstige:</label>
            <textarea class="form-control" name="salaries" id="salaries" required oninput="this.value = this.value.replace(/[^0-9,]/g, '').replace(/,,+/g, ',');" pattern="^(\d+)(,\d+)*$" title="Only numbers separated by commas are allowed.">{{ implode(',', $salaryLadder->salaries) }}</textarea>
            <div class="my-4">Flytt Stige:
                <a href="#" class="btn btn-success btn-sm" _="
                                on click
                                set numbersText to #salaries.value
                                set numbersArray to numbersText.split(',')
                                set incrementedNumbers to []
                                repeat for number in numbersArray
                                set incrementedNumber to (number as Number) -1
                                append incrementedNumber to incrementedNumbers
                                end
                                set #salaries.value to incrementedNumbers.join(',')
                                ">
                    Reduser Tallene
                </a>
                <a href="#"class="btn btn-success btn-sm" _="
                                on click
                                set numbersText to #salaries.value
                                set numbersArray to numbersText.split(',')
                                set incrementedNumbers to []
                                repeat for number in numbersArray
                                set incrementedNumber to (number as Number) + 1
                                append incrementedNumber to incrementedNumbers
                                end
                                set #salaries.value to incrementedNumbers.join(',')
                                ">
                    Increase Numbers
                </a>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Oppdater</button>
    </form>
@endsection

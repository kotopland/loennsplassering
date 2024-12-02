@extends('layouts.app')

@section('content')
    <h2>Endre Lønnsstige</h2>

    <form action="{{ route('admin.salary-ladders.update', $salaryLadder) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="ladder" class="form-label">Stige:</label>
            <input type="text" class="form-control" name="ladder" id="ladder" value="{{ $salaryLadder->ladder }}" required>
        </div>
        <div class="mb-3">
            <label for="group" class="form-label">Gruppe:</label>
            <input type="number" class="form-control" name="group" id="group" value="{{ $salaryLadder->group }}" required>
        </div>
        <div class="mb-3">
            <label for="salaries" class="form-label">Lønnsstige:</label>
            <textarea class="form-control" name="salaries" id="salaries" required oninput="this.value = this.value.replace(/[^0-9,]/g, '').replace(/,,+/g, ',');" pattern="^(\d+)(,\d+)*$" title="Only numbers separated by commas are allowed.">{{ implode(',', $salaryLadder->salaries) }}</textarea>
            <a href="#" class="btn btn-primary btn-sm" _="
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
                Decrease Numbers
            </a>
            <a href="#"class="btn btn-primary btn-sm" _="
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

        <button type="submit" class="btn btn-primary">Oppdater</button>
    </form>
@endsection

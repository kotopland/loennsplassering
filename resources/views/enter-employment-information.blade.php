@extends('layouts.app')

@section('content')
    @if (session()->has('message'))
        <p class="alert {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
    @endif

    <form action="{{ route('post-employment-information', $application) }}" method="POST" id="salary_form">
        @csrf
        <h2>Informasjon om stillingen</h2>
        <label class="form-label" for="job_title">Job Title:</label>
        <select class="form-control" name="job_title" id="job_title">
            @foreach ($positionsLaddersGroups as $position => $positionArray)
                <option value="{{ $position }}" @if (old('job_title', $application->job_title) === $position) selected @endif>{{ $position }}</option>
            @endforeach
        </select>
        <div>
            <label class="form-label" for="birth_date">Fødselsdato:</label>
            <input type="date" class="form-control" name="birth_date" id="birth_date" required value="{{ old('birth_date', $application['birth_date']) }}">
        </div>
        <div>
            <label class="form-label" for="birth_date">Starter i stillingen fra:</label>
            <input type="date" class="form-control" name="work_start_date" id="work_start_date" required value="{{ old('work_start_date', $application['work_start_date']) }}">
        </div>
        </div>
        <div class="fixed-bottom sticky-top text-md-end text-center pb-1">
            <a class="btn btn-outline-secondary " href="{{ url('/') }}">
                Til forsiden
            </a>
            <button type="submit" class="btn btn-success">
                Neste: Din utdanning
            </button>
        </div>
    </form>
@endsection

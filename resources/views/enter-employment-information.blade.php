@extends('layouts.app')

@section('content')
    @if (session()->has('message'))
        <p class="alert {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
    @endif

    <form action="{{ route('post-employment-information', $application) }}" method="POST" id="salary_form">
        @csrf
        <div class="col-12 col-md-6">
            <h2>Informasjon om stillingen</h2>
        </div>
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
        <a class="btn btn-outline-secondary " href="{{ url('/') }}" _="on click
                     if not confirm('Vil du starte på nytt? Ønsker du å beholde skjemaet, må du først bokmerke eller få sendt skjemaet til din e-post adresse.')
                     halt">Start helt på nytt</a>
        <button type="submit" class="btn btn-success">Neste: Din utdanning</button>
    </form>
@endsection

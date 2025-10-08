@extends('layouts.app')

@section('content')
    @if (session()->has('message'))
        <p class="alert my-2 {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
    @endif
    <div class="progress" role="progressbar" aria-label="Success example" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar bg-success" style="width: 20%">20%</div>
    </div>
    <form action="{{ route('post-employment-information', $application) }}" method="POST" id="salaryForm" novalidate>
        @csrf
        <h2>
            Informasjon om stillingen
        </h2>

        <!-- Workplace -->
        <label class="form-label" for="job_title">Type stilling:</label>
        <select class="form-control" name="job_title" id="job_title" required tabindex="1" @if ($application->isReadOnly()) disabled @endif>
            @if (!key_exists($application->job_title, $positionsLaddersGroups))
                <option value="">Velg fra listen</option>
            @endif
            @foreach ($positionsLaddersGroups as $position => $positionArray)
                <option value="{{ $position }}" @if (old('job_title', $application->job_title) === $position) selected @endif>
                    {{ $position }} ({{ $positionArray['ladder'] . $positionArray['group'] }}) {{ $positionArray['description'] }}
                </option>
            @endforeach
        </select>
        <div class="invalid-feedback">Vennligst velg en type stilling.</div>

        <div class="my-4">
            <label class="form-label" for="birth_date">Fødselsdato:</label>
            <input type="date" class="form-control" name="birth_date" id="birth_date" required value="{{ old('birth_date', $application['birth_date']) }}" tabindex="2" @if ($application->isReadOnly()) disabled @endif>
            <div class="invalid-feedback">Vennligst fyll ut fødselsdato.</div>
        </div>

        <div>
            <label class="form-label" for="work_start_date">Starter i stillingen fra:</label>
            <input type="date" class="form-control" name="work_start_date" id="work_start_date" required value="{{ old('work_start_date', $application['work_start_date']) }}" tabindex="3" @if ($application->isReadOnly()) disabled @endif>
            <div class="invalid-feedback">Vennligst fyll ut startdato for stillingen.</div>
        </div>
        <div class="fixed-bottom sticky-top text-md-end text-center pb-1 my-4">
            <a class="btn btn-outline-primary" href="{{ url('/') }}" tabindex="5" _="on click put 'Vennligst vent...' into my.innerHTML then wait 20ms then add .disabled to me end">Til forsiden</a>
            @if (!$application->isReadOnly())
                <button type="submit" class="btn btn-primary" tabindex="4" _="on click if #salaryForm.checkValidity() then put 'Vennligst vent...' into my.innerHTML then wait 20ms then add @@disabled to me end">Neste: Din utdanning</button>
            @else
                <a href={{ route('enter-education-information', $application) }} class="btn btn-primary" tabindex="4">Neste: Din utdanning</a>
            @endif
        </div>
    </form>
    <div class="py-5"></div>
    <div class="py-5"></div>
    <div class="py-5"></div>
@endsection

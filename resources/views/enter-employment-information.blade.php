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

        <!-- Workplace Category -->
        <div class="my-4">
            <label class="form-label">Velg arbeidssted:</label>
            <div class="btn-group" role="group" aria-label="Arbeidssted">
                @foreach (['Menighet', 'FriBU', 'Hovedkontoret'] as $category)
                    <input type="radio" class="btn-check" name="workplace_category" id="category_{{ $category }}" value="{{ $category }}" autocomplete="off" @if (old('workplace_category', $application->getWorkplaceCategory()) === $category) checked @endif @if ($application->isReadOnly()) disabled @endif>
                    <label class="btn btn-outline-primary" for="category_{{ $category }}">{{ $category }}</label>
                @endforeach
            </div>
        </div>

        <!-- Job Title Dropdown -->
        <div class="my-4" id="job_title_wrapper" style="display: none;">
            <label class="form-label" for="job_title">Type stilling:</label>
            <select class="form-control" name="job_title" id="job_title" required tabindex="1" @if ($application->isReadOnly()) disabled @endif>
                <option value="">Velg fra listen</option>
            </select>
            <div class="invalid-feedback">Vennligst velg en type stilling.</div>
        </div>

        <!-- Birth Date -->
        <div class="my-4">
            <label class="form-label" for="birth_date">Fødselsdato:</label>
            <input type="date" class="form-control" name="birth_date" id="birth_date" required value="{{ old('birth_date', $application['birth_date']) }}" tabindex="2" @if ($application->isReadOnly()) disabled @endif>
            <div class="invalid-feedback">Vennligst fyll ut fødselsdato.</div>
        </div>

        <!-- Work Start Date -->
        <div>
            <label class="form-label" for="work_start_date">Starter i stillingen fra:</label>
            <input type="date" class="form-control" name="work_start_date" id="work_start_date" required value="{{ old('work_start_date', $application['work_start_date']) }}" tabindex="3" @if ($application->isReadOnly()) disabled @endif>
            <div class="invalid-feedback">Vennligst fyll ut startdato for stillingen.</div>
        </div>

        <!-- Submit buttons -->
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const positionsByGroup = @json($groupedPositions);
                const categoryRadios = document.querySelectorAll('input[name="workplace_category"]');
                const jobTitleWrapper = document.getElementById('job_title_wrapper');
                const jobTitleSelect = document.getElementById('job_title');
                const selectedJobTitle = "{{ old('job_title', $application->job_title) }}";

                function updateJobTitles(selectedCategory) {
                    jobTitleSelect.innerHTML = '<option value="">Velg fra listen</option>';
                    const positions = positionsByGroup[selectedCategory];

                    if (positions) {
                        for (const position in positions) {
                            const details = positions[position];
                            const option = document.createElement('option');
                            option.value = position;
                            option.textContent =
                                `${position} (${details.ladder}${details.group}) ${details.description || ''}`;
                            if (position === selectedJobTitle) {
                                option.selected = true;
                            }
                            jobTitleSelect.appendChild(option);
                        }
                        jobTitleWrapper.style.display = 'block';
                    } else {
                        jobTitleWrapper.style.display = 'none';
                    }
                }

                categoryRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        updateJobTitles(this.value);
                    });
                });

                // Initial load
                const checkedRadio = document.querySelector('input[name="workplace_category"]:checked');
                if (checkedRadio) {
                    updateJobTitles(checkedRadio.value);
                }
            });
        </script>
    @endpush
@endsection

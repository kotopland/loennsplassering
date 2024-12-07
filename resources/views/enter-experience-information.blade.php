@extends('layouts.app')

@section('content')
    <div class="progress" role="progressbar" aria-label="Success example" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar bg-success" style="width: 60%">60%</div>
    </div>
    <h2>
        Arbeidserfaring
    </h2>

    @if ($hasErrors)
        <div class="callout callout-danger bg-danger-subtle">
            Det er noen mangler i registrerte opplysninger. Vennligst oppdater dem.
        </div>
    @endif

    <div class="mb-2 py-3">
        <div class="vstack gap-3">

            @isset($application->work_experience)
                <div>
                    <table class="table table-sm w-100 responsivetable">
                        <thead>
                            <tr>
                                <th scope="col">Tittel og arbeidssted</th>
                                <th scope="col">Stillingsprosent</th>
                                <th scope="col">Fra</th>
                                <th scope="col">Til</th>
                                <th scope="col">Relevanse</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($application->work_experience as $id => $item)
                                @if (request()->has('edit') && request()->edit == $id)
                                    <tr id="update">
                                        <td colspan="10">
                                            <form action="{{ route('update-single-experience-information', ['edit' => $id]) }}" method="POST" id="salary_form">
                                                @csrf

                                                <div class="row g-3 my-2 border border-primary border-2 bg-info p-2 p-md-4">
                                                    <h4 class="mb-4">Endre erfaring:</h4>
                                                    <div class="row">
                                                        <!-- Title and Workplace -->
                                                        <div class="col-6 col-md-3">
                                                            <label for="update_title_workplace" class="form-check-label">Tittel og arbeidssted:</label>
                                                            <textarea id="update_title_workplace" name="title_workplace" _="on keydown[event.key == 'Enter'] halt" class="form-control @error('title_workplace') is-invalid @enderror">{{ old('title_workplace', $item['title_workplace']) }}</textarea>
                                                            @error('title_workplace')
                                                                <div class="alert alert-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        <!-- Work Percentage -->
                                                        <div class="col-6 col-md-3">
                                                            <label for="update_percentage" class="form-check-label">Stillingsprosent:</label>
                                                            <input type="number" id="update_percentage" name="percentage" min="0" max="100" value="{{ old('percentage', $item['percentage']) }}" required class="form-control @error('percentage') is-invalid @enderror" placeholder="%" style="max-width: 100px">
                                                        </div>

                                                        <div class="col-12 col-md-6">
                                                            <div class="row justify-content-center">
                                                                <!-- Start Date -->
                                                                <div class="col-6 ">
                                                                    <label for="update_start_date" class="form-check-label">Ansatt
                                                                        fra:</label>
                                                                    <input type="date" id="update_start_date" name="start_date" min="1950-01-01" max="{{ date('Y-m-d') }}" value="{{ old('start_date', $item['start_date']) }}" class="form-control @error('start_date') is-invalid @enderror" style="max-width: 150px">
                                                                    @error('start_date')
                                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>

                                                                <!-- End Date -->
                                                                <div class="col-6 ">
                                                                    <label for="update_end_date" class="form-check-label">Ansatt
                                                                        til:</label>
                                                                    <input type="date" id="update_end_date" name="end_date" min="1950-01-01" max="{{ \Carbon\Carbon::parse($application->work_start_date)->subDay() }}" value="{{ old('end_date', $item['end_date']) }}" class="form-control @error('end_date') is-invalid @enderror" aria-describedby="endDateHelpBlock" style="max-width: 150px">
                                                                    <div id="endDateHelpBlock" class="form-text">
                                                                        (maksimalt {{ $application->work_start_date }})
                                                                    </div>
                                                                    @error('end_date')
                                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Workplace Type Section -->
                                                        <div class="pe-4 mt-4"><strong>Type arbeidssted:</strong></div>
                                                        <div class="row p-2 ms-4">
                                                            <div class="col-auto p-2 pe-4">
                                                                <input type="radio" id="update_normal" name="workplace_type" value="" class="form-check-input @error('workplace_type') is-invalid @enderror" @if (old('workplace_type', $item['workplace_type'] ?? '') === '') checked @endif>
                                                                <label for="update_normal" class="form-check-label">Ikke kristen
                                                                    organisasjon/kirke</label>
                                                            </div>

                                                            <div class="col-auto p-2 pe-4">
                                                                <input type="radio" id="update_freechurch" name="workplace_type" value="freechurch" class="form-check-input @error('workplace_type') is-invalid @enderror" @if (old('workplace_type', $item['workplace_type'] ?? '') === 'freechurch') checked @endif>
                                                                <label for="update_freechurch" class="form-check-label">Frikirken</label>
                                                            </div>

                                                            <div class="col-auto p-2 pe-4">
                                                                <input type="radio" id="update_other_christian" name="workplace_type" value="other_christian" class="form-check-input @error('workplace_type') is-invalid @enderror" @if (old('workplace_type', $item['workplace_type'] ?? '') === 'other_christian') checked @endif>
                                                                <label for="update_other_christian" class="form-check-label">Annen
                                                                    kristen
                                                                    organisasjon/kirke</label>
                                                            </div>

                                                            <div class="col-auto p-2 pe-4">
                                                                <input type="radio" id="update_elder" name="workplace_type" value="elder" class="form-check-input @error('workplace_type') is-invalid @enderror" @if (old('workplace_type', $item['workplace_type'] ?? '') === 'elder') checked @endif>
                                                                <label for="update_elder" class="form-check-label">Eldste i
                                                                    Frikirken</label>
                                                            </div>
                                                        </div>

                                                        <!-- Relevance  -->
                                                        <div class="row p-2">
                                                            <div class="col-auto p-2 pe-4">
                                                                <div class="form-check form-switch px-1 my-2">
                                                                    <input type="checkbox" id="update_relevance" name="relevance" role="switch" value="true" class="form-check-input" @if (old('relevance', $item['relevance'] ?? '') != false) checked @endif>
                                                                    <label for="update_relevance" class="form-check-label">
                                                                        Relevant for stillingen du skal inn i?
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Submit Button -->
                                                        <div class="row p-2">
                                                            <div class="col-auto p-2 pe-4">
                                                                <input type="submit" id="update-btn-submit" name="submit" value="Oppdater erfaring" class="btn btn-primary me-2 @if (null === old('title_workplace', $item['title_workplace'])) disabled @endif">
                                                                <a href="{{ route('enter-experience-information') }}" class="btn btn-sm btn-outline-primary">Tilbake</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endif

                                <tr>
                                    <th id="title_workplace-{{ $id }}">{{ strlen($item['title_workplace']) > 30 ? substr($item['title_workplace'], 0, 30) . '...' : $item['title_workplace'] }}</th>
                                    <td id="percentage-{{ $id }}"><span>@lang('Stillingsprosent'): </span>{{ $item['percentage'] }}{{ is_numeric($item['percentage']) ? '%' : '' }}</td>
                                    <td id="start_date-{{ $id }}"><span>@lang('Fra'): </span>{{ $item['start_date'] }}</td>
                                    <td id="end_date-{{ $id }}"><span>@lang('Til'): </span>{{ $item['end_date'] }}</td>
                                    <td id="relevance-{{ $id }}">{{ @$item['relevance'] == true ? 'relevant' : '' }}</td>
                                    <td>
                                        <a class="btn btn-sm @if (in_array(null, [@$item['title_workplace'], @$item['percentage'] == '' ? null : '', @$item['start_date'] == '' ? null : '', @$item['end_date'] == '' ? null : '', @$item['relevance']], true)) btn-danger @else btn-outline-primary @endif" href="{{ route('enter-experience-information', [$application, 'edit' => $id]) }}#update">
                                            @if (in_array(null, [@$item['title_workplace'], @$item['percentage'] == '' ? null : '', @$item['start_date'] == '' ? null : '', @$item['end_date'] == '' ? null : '', @$item['relevance']], true))
                                                Vennligst oppdater
                                            @else
                                                Endre
                                            @endif
                                        </a>

                                        {{-- <td><a class=" btn btn-sm btn-outline-primary" href="#"
                                _="on click set the value of #title_workplace to the innerText of #title_workplace-{{ $id }} then set the value of #workplace_type to the innerText of #workplace_type-{{ $id }} then set the value of #percentage to the innerText of #percentage-{{ $id }} then set the value of #start_date to the innerText of #start_date-{{ $id }} then set the value of #end_date to the innerText of #end_date-{{ $id }} then set the value of #study_points to the innerText of #study_points-{{ $id }} then add .disabled to #btn-next then remove .disabled from #btn-submit">Lag
                                ny basert på denne</a>
                        </td> --}}
                                        <a class="btn btn-sm btn-outline-danger" href={{ route('destroy-experience-information', ['id' => $id]) }}>Slett linje</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endisset
            <div class="mt-5">
                <form action="{{ route('post-experience-information') }}" method="POST" id="salary_form">
                    @csrf

                    <div class="row g-3 mb-2 border border-primary border-2 bg-success-subtle p-2 p-md-4">
                        <h4 class="mb-4">Legg til erfaring:</h4>
                        <div class="row">
                            <div class="col-auto">
                                <label class="form-check-label" for="title_workplace">Tittel og arbeidssted:</label>
                                <textarea id="title_workplace" name="title_workplace" placeholder="" _="on keyup if my.value is not empty add .disabled to #btn-next then remove .disabled from #btn-submit else remove .disabled from #btn-next then add .disabled to #btn-submit end on keydown[event.key == 'Enter'] halt" class="form-control @error('title_workplace') is-invalid @enderror">{{ old('title_workplace') }}</textarea>
                                @error('title_workplace')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-auto pe-4">
                                <label class="form-check-label" for="percentage">Stillingsprosent:</label>
                                <input type="number" min="0" max="100" required id="percentage" name="percentage" value="{{ old('percentage') }}" placeholder="%" class="form-control @error('percentage') is-invalid @enderror">
                            </div>
                            <div class="col-auto">
                                <div class="row justify-content-center">
                                    <div class="col-auto pe-4">
                                        <label class="form-check-label" for="start_date">Ansatt fra:</label>
                                        <input type="date" id="start_date" name="start_date" min="1950-01-01" value="{{ old('start_date') }}" max="{{ date('Y-m-d') }}" class="form-control @error('start_date') is-invalid @enderror" style="max-width: 150px;">
                                        @error('start_date')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-auto pe-4">
                                        <label class="form-check-label" for="end_date">Ansatt til:</label>
                                        <input type="date" id="end_date" name="end_date" min="1950-01-01" value="{{ old('end_date') }}" max="{{ \Carbon\Carbon::parse($application->work_start_date)->subDay() }}" value="{{ old('end_date') }}" class="form-control @error('end_date') is-invalid @enderror" aria-describedby="endDateHelpBlock" style="max-width: 150px;">
                                        <div id="endDateHelpBlock" class="form-text">(er du fortsatt i stillingen skriver du
                                            dato for tiltredelse i ny stilling)</div>
                                        @error('end_date')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row p-2">
                            <div class="pe-4 mt-4"><strong>Type arbeidssted:</strong></div>

                            <div class="col-auto p-2  pe-4">
                                <input type="radio" class="form-check-input @error('workplace_type') is-invalid @enderror" id="normal" name="workplace_type" @if (!old('workplace_type')) checked @endif value="">
                                <label class="form-check-label" for="normal">Ikke kristen organisasjon/kirke</label>
                            </div>
                            <div class="col-auto p-2  pe-4">
                                <input type="radio" class="form-check-input @error('workplace_type') is-invalid @enderror" id="freechurch" name="workplace_type" value="freechurch" @if (old('workplace_type') === 'freechurch') checked @endif>
                                <label class="form-check-label" for="freechurch">Frikirken</label>
                            </div>
                            <div class="col-auto p-2  pe-4">
                                <input type="radio" class="form-check-input @error('workplace_type') is-invalid @enderror" id="other_christian" name="workplace_type" value="other_christian" @if (old('workplace_type') === 'other_christian') checked @endif>
                                <label class="form-check-label" for="other_christian">Annen kristen
                                    organisasjon/kirke</label>
                            </div>
                            <div class="col-auto p-2  pe-4">
                                <input type="radio" class="form-check-input @error('workplace_type') is-invalid @enderror" id="elder" name="workplace_type" value="elder" @if (old('workplace_type') === 'elder') checked @endif>
                                <label class="form-check-label" for="elder">Eldste i Frikirken</label>
                            </div>
                        </div>
                        <div class="row p-2">
                            <div class="col-auto p-2 pe-4">
                                <div class="form-check form-switch px-1 my-2">
                                    <input type="checkbox" class="form-check-input" role="switch" id="relevance" name="relevance" value="true">
                                    <label class="form-check-label" for="relevance">
                                        Relevant for stillingen du skal inn i?
                                    </label>
                                </div>

                                <div class="col-auto p-2 pe-4">
                                    <input type="submit" class="form-control-input btn btn-sm btn-primary @if (null === old('topic_and_school')) disabled @endif" id="btn-submit" name="submit" value="Registrer erfaring">
                                </div>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <div class="text-md-end text-center pb-1">
        <a href={{ route('enter-education-information', $application) }} class="btn btn-outline-primary my-2" tabindex="99">
            Forrige side
        </a>
        @if ($hasErrors)
            <a href="{{ route('enter-courses-and-activity-information', $application) }}" class="btn btn-primary my-2 disabled" id="btn-next">
                Neste: Kurs og Verv
            </a>
            <span class="badge text-bg-danger">Du må oppdatere felt med mangler før du kan gå videre</span>
        @else
            <a href="{{ route('enter-courses-and-activity-information', $application) }}" class="btn btn-primary my-2" id="btn-next">
                Neste: Kurs og Verv
            </a>
        @endif
    </div>
@endsection

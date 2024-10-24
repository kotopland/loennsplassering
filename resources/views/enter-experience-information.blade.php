@extends('layouts.app')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="my-2 py-3">
        <h2>Arbeidserfaring</h2>
        <div class="vstack gap-3">

            @isset($application->work_experience)
                <div>
                    <table class="table table-sm w-100">
                        <thead>
                            <tr>
                                <th scope="col">Tittel og arbeidssted</th>
                                <th scope="col">Stillingsprosent</th>
                                <th scope="col">Fra</th>
                                <th scope="col">Til</th>
                                <th scope="col">Relevanse</th>
                                <th scope="col"></th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($application->work_experience as $id => $item)
                                @if (request()->has('edit') && request()->edit == $id)
                                    <tr>
                                        <td colspan="10">
                                            <form action="{{ route('update-single-experience-information', ['edit' => $id]) }}" method="POST" id="salary_form">
                                                @csrf

                                                <div class="row g-3 mb-2 border border-secondary border-1 bg-primary-subtle my-2 p-2">
                                                    <h5 class="mb-4">Endre erfaring:</h5>

                                                    <div class="row">
                                                        <!-- Title and Workplace -->
                                                        <div class="col-6 col-md-3">
                                                            <label for="title_workplace" class="form-check-label">Tittel og arbeidssted:</label>
                                                            <input type="text" id="title_workplace" name="title_workplace" value="{{ old('title_workplace', $item['title_workplace']) }}" class="form-control @error('title_workplace') is-invalid @enderror" placeholder="Skriv inn tittel og arbeidssted">
                                                            @error('title_workplace')
                                                                <div class="alert alert-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        <!-- Work Percentage -->
                                                        <div class="col-6 col-md-3">
                                                            <label for="work_percentage" class="form-check-label">Stillingsprosent:</label>
                                                            <input type="number" id="work_percentage" name="work_percentage" min="0" max="100" value="{{ old('work_percentage', $item['work_percentage']) }}" required class="form-control @error('work_percentage') is-invalid @enderror" placeholder="%" style="max-width: 100px">
                                                        </div>
                                                        <div class="col-6 col-md-3">
                                                            <div class="row justify-content-center">
                                                                <!-- Start Date -->
                                                                <div class="col-6 col-md-auto">
                                                                    <label for="start_date" class="form-check-label">Ansatt fra:</label>
                                                                    <input type="date" id="start_date" name="start_date" min="2000-01-01" max="{{ date('Y-m-d') }}" value="{{ old('start_date', $item['start_date']) }}" class="form-control @error('start_date') is-invalid @enderror">
                                                                    @error('start_date')
                                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>

                                                                <!-- End Date -->
                                                                <div class="col-6 col-md-auto">
                                                                    <label for="end_date" class="form-check-label">Ansatt til:</label>
                                                                    <input type="date" id="end_date" name="end_date" min="2000-01-01" max="{{ \Carbon\Carbon::parse($application->work_start_date)->subDay() }}" value="{{ old('end_date', $item['end_date']) }}" class="form-control @error('end_date') is-invalid @enderror" aria-describedby="endDateHelpBlock">
                                                                    <div id="endDateHelpBlock" class="form-text">
                                                                        (Er du fortsatt i stillingen, skriv dato for tiltredelse i ny stilling)
                                                                    </div>
                                                                    @error('end_date')
                                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Workplace Type Section -->
                                                    <div class="row p-2">
                                                        <div class="col-auto p-2 pe-4">
                                                            <input type="radio" id="normal" name="workplace_type" value="" class="form-check-input @error('workplace_type') is-invalid @enderror" @if (old('workplace_type', $item['workplace_type'] ?? '') === '') checked @endif>
                                                            <label for="normal" class="form-check-label">Ikke kristen organisasjon/kirke</label>
                                                        </div>

                                                        <div class="col-auto p-2 pe-4">
                                                            <input type="radio" id="freechurch" name="workplace_type" value="freechurch" class="form-check-input @error('workplace_type') is-invalid @enderror" @if (old('workplace_type', $item['workplace_type'] ?? '') === 'freechurch') checked @endif>
                                                            <label for="freechurch" class="form-check-label">Frikirken</label>
                                                        </div>

                                                        <div class="col-auto p-2 pe-4">
                                                            <input type="radio" id="other_christian" name="workplace_type" value="other_christian" class="form-check-input @error('workplace_type') is-invalid @enderror" @if (old('workplace_type', $item['workplace_type'] ?? '') === 'other_christian') checked @endif>
                                                            <label for="other_christian" class="form-check-label">Annen kristen organisasjon/kirke</label>
                                                        </div>

                                                        <div class="col-auto p-2 pe-4">
                                                            <input type="radio" id="elder" name="workplace_type" value="elder" class="form-check-input @error('workplace_type') is-invalid @enderror" @if (old('workplace_type', $item['workplace_type'] ?? '') === 'elder') checked @endif>
                                                            <label for="elder" class="form-check-label">Eldste i Frikirken</label>
                                                        </div>
                                                    </div>

                                                    <!-- Relevance Checkbox and Submit Button -->
                                                    <div class="row p-2">
                                                        <div class="col-auto p-2 pe-4">
                                                            <input type="checkbox" id="relevance" name="relevance" value="true" class="form-check-input" @if (old('relevance', $item['relevance'] ?? '') == 1) checked @endif>
                                                            <label for="relevance" class="form-check-label">
                                                                Særdeles høy relevanse for stillingen du skal inn i?
                                                            </label>
                                                        </div>

                                                        <div class="col-auto p-2 pe-4">
                                                            <input type="submit" id="btn-submit" name="submit" value="Registrer erfaring" class="btn btn-sm btn-primary @if (null === old('title_workplace', $item['title_workplace'])) disabled @endif">
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endif

                                <tr>
                                    <th id="title_workplace-{{ $id }}" scope="row">{{ $item['title_workplace'] }}</th>
                                    <td id="work_percentage-{{ $id }}">{{ $item['work_percentage'] }}{{ is_numeric($item['work_percentage']) ? '%' : '' }}</td>
                                    <td id="start_date-{{ $id }}">{{ $item['start_date'] }}</td>
                                    <td id="end_date-{{ $id }}">{{ $item['end_date'] }}</td>
                                    <td id="relevance-{{ $id }}">{{ @$item['relevance'] == true ? 'relevant' : '' }}</td>
                                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('enter-experience-information', [$application, 'edit' => $id]) }}"">Endre</a></td>
                                    <td><a class="btn btn-sm btn-outline-primary" href="#" _="on click set the value of #title_workplace to the innerText of #title_workplace-{{ $id }} then {{-- set the value of #workplace_type to the innerText of #workplace_type-{{ $id }} then --}} set the value of #work_percentage to the innerText of #work_percentage-{{ $id }} then set the value of #start_date to the innerText of #start_date-{{ $id }} then set the value of #end_date to the innerText of #end_date-{{ $id }} then {{-- set the value of #study_points to the innerText of #study_points-{{ $id }} then --}} {{-- set the value of #study_percentage to the innerText of #study_percentage-{{ $id }} then --}} {{-- set the value of #relevance to the innerText of #relevance-{{ $id }} then --}} add .disabled to #btn-next then remove .disabled from #btn-submit">Lag ny basert på denne</a></td>
                                    <td><a class="btn btn-sm btn-outline-danger" href={{ route('destroy-experience-information', ['id' => $id]) }}>Slett linje</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endisset
            <div>
                <form action="{{ route('post-experience-information') }}" method="POST" id="salary_form">
                    @csrf

                    <div class="row g-3 mb-2 border border-secondary border-1 bg-secondary-subtle m-2 p-2">
                        <h5 class="mb-4">Legg til erfaring:</h5>
                        <div class="row">
                            <div class="col-auto">
                                <label class="form-check-label" for="title_workplace">Tittel og arbeidssted:</label>
                                <input type="text" id="title_workplace" name="title_workplace" value="{{ old('title_workplace') }}" placeholder="" _="on keyup if my.value is not empty add .disabled to #btn-next then remove .disabled from #btn-submit else remove .disabled from #btn-next then add .disabled to #btn-submit end" class="form-control @error('title_workplace') is-invalid @enderror">
                                @error('title_workplace')
                                    <div class="alert alert-danger">{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-auto pe-4">
                                <label class="form-check-label" for="work_percentage">Stillingsprosent:</label>
                                <input type="number" min="0" max="100" required id="work_percentage" name="work_percentage" value="{{ old('work_percentage') }}" placeholder="%" class="form-control @error('work_percentage') is-invalid @enderror">
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
                                        <div id="endDateHelpBlock" class="form-text">(er du fortsatt i stillingen skriver du dato for tiltredelse i ny stilling)</div>
                                        @error('end_date')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row p-2">

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
                                <label class="form-check-label" for="other_christian">Annen kristen organisasjon/kirke</label>
                            </div>
                            <div class="col-auto p-2  pe-4">
                                <input type="radio" class="form-check-input @error('workplace_type') is-invalid @enderror" id="elder" name="workplace_type" value="elder" @if (old('workplace_type') === 'elder') checked @endif>
                                <label class="form-check-label" for="elder">Eldste i Frikirken</label>
                            </div>
                        </div>
                        <div class="row p-2">
                            <div class="col-auto p-2  pe-4">
                                <input type="checkbox" class="form-check-input" id="relevance" name="relevance" value="true">
                                <label class="form-check-label" for="relevance">Særdeles høy relevanse for stillingen du skal inn i?</label>
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
    <div class="my-2 py-3">

        <h4>Kurs, verv og frivillig arbeid, relevant for stillingen. </h4>
        <p>Denne kalkulatoren kan ikke beregne frivillig arbeid automatsik. Normalt sett gies det bare ansiennitet eller kompetansetillegg i særtilfeller der det er brukt mye tid utover normal menighets- og organisasjonsliv. Er du eldste eller har vært kan du skrives det som en 25% stilling.</p>
    </div>
    <a href={{ route('enter-employment-information', $application) }} class="btn btn-sm btn-secondary">Forrige: Informasjon om stillingen</a>
    <a href="{{ route('preview-and-estimated-salary', $application) }}" class="btn btn-success" id="btn-next">
        Neste: Sammendrag og estimert lønnsplassering
    </a>
    </div>
@endsection

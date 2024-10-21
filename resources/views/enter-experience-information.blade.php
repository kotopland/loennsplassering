<!DOCTYPE html>
<html>

<head>
    <title>Salary Calculator</title>
    <script src="https://unpkg.com/hyperscript.org@0.9.11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>

<body>
    <div class="container">
        @if (session()->has('message'))
            <p class="alert {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
        @endif

        <div class="container">
            <h1>Salary Calculator</h1>

            <div class="my-2 py-3">
                <h2>Arbeidserfaring</h2>
                <div class="vstack gap-3">

                    @isset($employeeCV->work_experience)
                        <div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Tittel og arbeidssted</th>
                                        <th scope="col">Kristen organisasjon</th>
                                        <th scope="col">Frikirken</th>
                                        <th scope="col">Stillingsprosent</th>
                                        <th scope="col">Fra</th>
                                        <th scope="col">Til</th>
                                        <th scope="col">Relevanse</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($employeeCV->work_experience as $id => $item)
                                        <tr>
                                            <th scope="row">{{ $item['title_workplace'] }}</th>
                                            <td>{{ @$item['workplace_type'] }}</td>
                                            <td>{{ $item['work_percentage'] }}</td>
                                            <td>{{ $item['start_date'] }}</td>
                                            <td>{{ $item['end_date'] }}</td>
                                            <td>{{ @$item['relevance'] == true ? 'relevant' : '' }}</td>
                                            <td><a href={{ route('destroy-experience-information', ['id' => $id]) }}>Slett linje</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endisset
                    <div>
                        <form action="{{ route('post-experience-information') }}" method="POST" id="salary_form">
                            @csrf

                            <div class="row g-3 mb-2 border border-1 m-2 p-2">
                                <div class="row">
                                    <div class="col-auto">
                                        <label class="form-check-label" for="title_workplace">Tittel og arbeidssted:</label>
                                        <input type="text" id="title_workplace" name="title_workplace" value="{{ old('title_workplace') }}" placeholder="" _="on keyup
          if my.value is not empty
             add .disabled to #btn-next then remove .disabled from #btn-submit
          else
             remove .disabled from #btn-next then add .disabled to #btn-submit
       end" class="form-control @error('title_workplace') is-invalid @enderror">
                                        @error('title_workplace')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-auto pe-4">
                                        <label class="form-check-label" for="work_percentage">Stillingsprosent:</label>
                                        <input type="number" min="0" max="100" required id="work_percentage" name="work_percentage" value="{{ old('work_percentage') }}" placeholder="%" class="form-control @error('work_percentage') is-invalid @enderror">
                                    </div>
                                    <div class="col-auto pe-4">
                                        <label class="form-check-label" for="start_date">Ansatt fra:</label>
                                        <input type="date" id="start_date" name="start_date" min="2000-01-01" value="{{ old('start_date') }}" max="{{ date('Y-m-d') }}" class="form-control @error('start_date') is-invalid @enderror">
                                        @error('start_date')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-auto pe-4">
                                        <label class="form-check-label" for="end_date">Ansatt til:</label>
                                        <input type="date" id="end_date" name="end_date" min="2000-01-01" value="{{ old('end_date') }}" max="{{ date('Y-m-d') }}" class="form-control @error('end_date') is-invalid @enderror" aria-describedby="endDateHelpBlock">
                                        <div id="endDateHelpBlock" class="form-text">(er du fortsatt i stillingen skriver du dato for tiltredelse i ny stilling)</div>
                                        @error('end_date')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
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
            <a href={{ route('enter-employment-information') }} class="btn btn-sm btn-secondary">Forrige: Informasjon om stillingen</a>
            <a href="{{ route('preview-and-estimated-salary') }}" class="btn btn-success" id="btn-next">
                Neste: Sammendrag og estimert lønnsplassering
            </a>
        </div>
    </div>

</body>

</html>

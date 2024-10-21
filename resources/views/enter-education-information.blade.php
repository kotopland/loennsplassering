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
        <h1>Salary Calculator</h1>

        <div class="my-2 py-3">
            <h2>Utdanning</h2>
            <div class="vstack gap-3">
                @isset($employeeCV->education)
                    <div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Utdanning</th>
                                    <th scope="col">Fra</th>
                                    <th scope="col">Til</th>
                                    <th scope="col">Studiepoeng/Bestått/Ikke fullført</th>
                                    <th scope="col">Studert i</th>
                                    <th scope="col">Grad</th>
                                    <th scope="col"></th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employeeCV->education as $id => $item)
                                    <tr>
                                        <th scope="row">{{ $item['topic_and_school'] }}</th>
                                        <td>{{ $item['start_date'] }}</td>
                                        <td>{{ $item['end_date'] }}</td>
                                        <td>{{ $item['study_points'] }}</td>
                                        <td>{{ @$item['study_percentage'] }}%</td>
                                        <td>{{ @$item['highereducation'] }}</td>
                                        <td>{{ @$item['relevance'] == true ? 'relevant' : '' }}</td>
                                        <td><a href={{ route('destroy-education-information', ['id' => $id]) }}>Slett linje</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endisset
                <div>
                    <form action="{{ route('post-education-information') }}" method="POST" id="salary_form">
                        @csrf

                        <div class="row g-3 mb-2 border border-1 m-2 p-2">
                            <div class="row">
                                <div class="col-auto">
                                    <input type="text" id="topic_and_school" name="topic_and_school" value="{{ old('topic_and_school') }}" placeholder="Navn på studiet og studiested" _="on keyup
          if my.value is not empty
             add .disabled to #btn-next then remove .disabled from #btn-submit
          else
             remove .disabled from #btn-next then add .disabled to #btn-submit
       end" class="form-control @error('topic_and_school') is-invalid @enderror">
                                    @error('topic_and_school')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-2">
                                    <input type="date" id="start_date" name="start_date" min="2000-01-01" value="{{ old('start_date') }}" max="{{ date('Y-m-d') }}" class="form-control @error('start_date') is-invalid @enderror">
                                    @error('start_date')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-2">
                                    <input type="date" id="end_date" name="end_date" min="2000-01-01" value="{{ old('end_date') }}" class="form-control @error('end_date') is-invalid @enderror">
                                    @error('end_date')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row g-3 mb-2">
                                <div class="col-auto pe-4">
                                    <select name="study_points" class="form-select @error('study_points') is-invalid @enderror">
                                        <option value="">Velg fra listen</option>
                                        <option value="30" @if (old('study_points') === '30') selected @endif>30</option>
                                        <option value="60" @if (old('study_points') === '60') selected @endif>60</option>
                                        <option value="120" @if (old('study_points') === '120') selected @endif>120</option>
                                        <option value="180" @if (old('study_points') === '180') selected @endif>180</option>
                                        <option value="240" @if (old('study_points') === '240') selected @endif>240</option>
                                        <option value="300" @if (old('study_points') === '300') selected @endif>300</option>
                                        <option value="0">Bestått</option>
                                    </select>
                                    @error('study_points')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-auto pe-4">
                                    <input type="radio" class="form-check-input @error('highereducation') is-invalid @enderror" id="no_degree" name="highereducation" @if (!old('highereducation')) checked @endif value="">
                                    <label class="form-check-label" for="no_degree">Uten grad</label>
                                </div>
                                <div class="col-auto pe-4">
                                    <input type="radio" class="form-check-input @error('highereducation') is-invalid @enderror" id="bachelor" name="highereducation" value="bachelor" @if (old('highereducation') === 'bachelor') checked @endif>
                                    <label class="form-check-label" for="bachelor">Høgskolenivå (4 år) eller bachelorgrad</label>
                                </div>
                                <div class="col-auto pe-4">
                                    <input type="radio" class="form-check-input @error('highereducation') is-invalid @enderror" id="master" name="highereducation" value="master" @if (old('highereducation') === 'master') checked @endif>
                                    <label class="form-check-label" for="master">Mastergradsnivå, siviltittel med videre</label>
                                </div>
                                <div class="col-auto pe-4">
                                    <input type="checkbox" class="form-check-input" id="relevant" name="relevance" value="true">
                                    <label class="form-check-label" for="relevant">Særdeles høy relevanse for stillingen?</label>
                                </div>
                                <div class="col-auto">
                                    <input type="submit" class="form-control-input btn btn-sm btn-primary @if (null === old('topic_and_school')) disabled @endif" id="btn-submit" name="submit" value="Registrer utdanning">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <a href={{ route('enter-employment-information') }} class="btn btn-sm btn-secondary">Forrige: Informasjon om stillingen</a>
        <a href="{{ route('enter-experience-information') }}" class="btn btn-success" id="btn-next">
            Neste: Din ansiennitet
        </a>
    </div>
</body>

</html>

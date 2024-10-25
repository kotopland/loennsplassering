@extends('layouts.app')

@section('content')

    <h2>Utdanning</h2>

    @if ($hasErrors)
        <div class="callout callout-danger bg-danger-subtle">
            Det er noen mangler i registrerte opplysninger. Vennligst oppdater dem.
        </div>
    @endif

    <div class="mb-2 py-3">
        <div class="vstack gap-3">
            @isset($application->education)
                <table class="table table-sm w-100">
                    <thead>
                        <tr>
                            <th scope="col">Utdanning</th>
                            <th scope="col">Fra</th>
                            <th scope="col">Til</th>
                            <th scope="col">Studiepoeng</th>
                            <th scope="col">% Studie</th>
                            <th scope="col">Grad</th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($application->education as $id => $item)
                            @if (request()->has('edit') && request()->edit == $id)
                                <tr>
                                    <td colspan="10">
                                        <form action="{{ route('update-single-education-information', ['edit' => $id]) }}" method="POST" id="salary_form">
                                            @csrf

                                            <div class="row g-3 mb-2 border border-secondary border-1 bg-primary-subtle my-2 p-2">
                                                <h5>Endre kompetanse</h5>
                                                <!-- Topic and School -->
                                                <div class="col-6 col-md-3">
                                                    <label for="topic_and_school" class="form-check-label">Studienavn og sted</label>
                                                    <input type="text" id="topic_and_school" name="topic_and_school" value="{{ old('topic_and_school', $item['topic_and_school']) }}" class="form-control @error('topic_and_school') is-invalid @enderror">
                                                    @error('topic_and_school')
                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <div class="row justify-content-center">
                                                        <!-- Start Date -->
                                                        <div class="col-6 col-md-auto">
                                                            <label for="start_date" class="form-check-label">Studiestart</label>
                                                            <input type="date" id="start_date" name="start_date" min="2000-01-01" value="{{ old('start_date', $item['start_date']) }}" max="{{ date('Y-m-d') }}" class="form-control @error('start_date') is-invalid @enderror" style="max-width: 150px;">
                                                            @error('start_date')
                                                                <div class="alert alert-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        <!-- End Date -->
                                                        <div class="col-6 col-md-auto">
                                                            <label for="end_date" class="form-check-label">Studieslutt</label>
                                                            <input type="date" id="end_date" name="end_date" min="2000-01-01" value="{{ old('end_date', $item['end_date']) }}" class="form-control @error('end_date') is-invalid @enderror" style="max-width: 150px;">
                                                            @error('end_date')
                                                                <div class="alert alert-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Study Points -->
                                                <div class="col-4 col-md-4">
                                                    <label for="study_points" class="form-check-label">Studiepoeng</label>
                                                    <select id="study_points" name="study_points" class="form-select @error('study_points') is-invalid @enderror" style="max-width: 100px">
                                                        <option value="">Velg</option>
                                                        @foreach ([10, 20, 30, 60, 120, 180, 240, 300, 360, 420] as $points)
                                                            <option value="{{ $points }}" @if (old('study_points', $item['study_points']) == $points) selected @endif>
                                                                {{ $points }}
                                                            </option>
                                                        @endforeach
                                                        <option value="bestått" @if (old('study_points', $item['study_points']) === 'bestått') selected @endif>
                                                            Bestått
                                                        </option>
                                                    </select>
                                                    @error('study_points')
                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <!-- Degree Section -->
                                                <div class="col-12 d-flex flex-wrap">
                                                    <div class="pe-4">Fullført grad:</div>
                                                    <div class="form-check pe-4">
                                                        <input type="radio" id="no_degree" name="highereducation" value="" class="form-check-input @error('highereducation') is-invalid @enderror" @if (empty(old('highereducation', $item['highereducation'] ?? ''))) checked @endif>
                                                        <label for="no_degree" class="form-check-label">Normalt studie, ingen grad</label>
                                                    </div>
                                                    <div class="form-check pe-4">
                                                        <input type="radio" id="bachelor" name="highereducation" value="bachelor" class="form-check-input @error('highereducation') is-invalid @enderror" @if (old('highereducation', $item['highereducation'] ?? '') === 'bachelor') checked @endif>
                                                        <label for="bachelor" class="form-check-label">Høgskolenivå (4 år) eller bachelorgrad</label>
                                                    </div>
                                                    <div class="form-check pe-4">
                                                        <input type="radio" id="master" name="highereducation" value="master" class="form-check-input @error('highereducation') is-invalid @enderror" @if (old('highereducation', $item['highereducation'] ?? '') === 'master') checked @endif>
                                                        <label for="master" class="form-check-label">Mastergradsnivå, siviltittel med videre</label>
                                                    </div>
                                                </div>

                                                <!-- Relevance and Submit Button -->
                                                <div class="col-12 d-flex flex-wrap align-items-center">
                                                    <input type="hidden" name="relevance" value="false">
                                                    <div class="form-check pe-4">
                                                        <input type="checkbox" id="relevant" name="relevance" value="true" @if (old('relevance', $item['relevance'] ?? '') == 1) checked @endif class="form-check-input">
                                                        <label for="relevant" class="form-check-label">Særdeles høy relevanse for stillingen?</label>
                                                    </div>
                                                    <input type="submit" id="btn-update" name="submit" value="Oppdater utdanning" class="btn btn-sm btn-primary @if (null === old('topic_and_school', $item['topic_and_school'])) disabled @endif">
                                                </div>

                                            </div>
                                        </form>

                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <th id="topic_and_school-{{ $id }}" scope="row">{{ strlen($item['topic_and_school']) > 30 ? substr($item['topic_and_school'], 0, 30) . '...' : $item['topic_and_school'] }}</th>
                                    <td id="start_date-{{ $id }}">{{ $item['start_date'] }}</td>
                                    <td id="end_date-{{ $id }}">{{ $item['end_date'] }}</td>
                                    <td id="study_points-{{ $id }}">{{ $item['study_points'] }}</td>
                                    <td id="study_percentage-{{ $id }}">{{ @$item['study_percentage'] }} {{ is_numeric($item['study_percentage']) ? '%' : '' }}</td>
                                    <td id="highereducation-{{ $id }}">{{ @$item['highereducation'] }}</td>
                                    <td id="relevance-{{ $id }}">{{ @$item['relevance'] == true ? 'relevant' : '' }}</td>
                                    <td>
                                        <a class="btn btn-sm @if (in_array(null, [@$item['topic_and_school'], @$item['start_date'], @$item['end_date'], @$item['study_points'], @$item['study_percentage'], @$item['relevance']], true)) btn-danger @else btn-outline-primary @endif" href="{{ route('enter-education-information', [$application, 'edit' => $id]) }}"">
                                            @if (in_array(null, [@$item['topic_and_school'], @$item['start_date'], @$item['end_date'], @$item['study_points'], @$item['study_percentage'], @$item['relevance']], true))
                                                Vennligst oppdater
                                            @else
                                                Endre
                                            @endif
                                        </a>
                                    </td>
                                    <td><a class="btn btn-sm btn-outline-primary" href="#" _="on click set the value of #topic_and_school to the innerText of #topic_and_school-{{ $id }} then set the value of #start_date to the innerText of #start_date-{{ $id }} then set the value of #end_date to the innerText of #end_date-{{ $id }} then add .disabled to #btn-next then remove .disabled from #btn-submit">Lag ny basert på denne</a></td>
                                    <td><a class="btn btn-sm btn-outline-danger" href="{{ route('destroy-education-information', ['id' => $id]) }}"">Slett linje</a></td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @endisset
            <div>
                <form action="{{ route('post-education-information') }}" method="POST" id="salary_form">
                    @csrf

                    <div class="row g-3 mb-2 border border-secondary border-1 bg-secondary-subtle m-2 p-2">
                        <h5 class="mb-4">Legg til kompetanse:</h5>
                        <div class="row">
                            <div class="col-auto">
                                <input type="text" id="topic_and_school" name="topic_and_school" value="{{ old('topic_and_school') }}" placeholder="Navn på studiet og studiested" _="on keyup if my.value is not empty add .disabled to #btn-next then remove .disabled from #btn-submit else remove .disabled from #btn-next then add .disabled to #btn-submit end" class="form-control @error('topic_and_school') is-invalid @enderror">
                                @error('topic_and_school')
                                    <div class="alert alert-danger">{{ $message }}
                                    </div>
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
                                    <option value="10" @if (old('study_points') === '10') selected @endif>10</option>
                                    <option value="20" @if (old('study_points') === '20') selected @endif>20</option>
                                    <option value="30" @if (old('study_points') === '30') selected @endif>30</option>
                                    <option value="60" @if (old('study_points') === '60') selected @endif>60</option>
                                    <option value="120" @if (old('study_points') === '120') selected @endif>120</option>
                                    <option value="180" @if (old('study_points') === '180') selected @endif>180</option>
                                    <option value="240" @if (old('study_points') === '240') selected @endif>240</option>
                                    <option value="300" @if (old('study_points') === '300') selected @endif>300</option>
                                    <option value="360" @if (old('study_points') === '360') selected @endif>360</option>
                                    <option value="420" @if (old('study_points') === '420') selected @endif>420</option>
                                    <option value="bestått" @if (old('study_points') === 'bestått') selected @endif>Bestått</option>
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
                                <input type="hidden" name="relevance" value="false">
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

    <div class="fixed-bottom sticky-top text-md-end text-center pb-1">
        <a href={{ route('enter-employment-information', $application) }} class="btn btn-sm btn-secondary">
            Forrige: Informasjon om stillingen
        </a>
        @if ($hasErrors)
            <a href="{{ route('enter-experience-information', $application) }}" class="btn btn-success disabled" id="btn-next">
                Neste: Din ansiennitet
            </a><br />
            Vennligst oppdater felt med mangler
        @else
            <a href="{{ route('enter-experience-information', $application) }}" class="btn btn-success" id="btn-next">
                Neste: Din ansiennitet
            </a>
        @endif
    </div>

@endsection

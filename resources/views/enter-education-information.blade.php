@extends('layouts.app')

@section('content')
<div class="progress" role="progressbar" aria-label="Success example" aria-valuenow="25" aria-valuemin="0"
    aria-valuemax="100">
    <div class="progress-bar bg-success" style="width: 40%">40%</div>
</div>
<h2>
    Utdanning
</h2>
<p>Dersom du har tatt en grad (bachelor- eller master-/sivilingeniørgrad) skal du samle alle utdanningene som hører til
    graden og registrere det samlet som en. Hver grad skal registreres hver for seg. Bachelor har normalt 180
    studiepoeng, og Master har
    180 studiepoeng. Annen utdanning skal registreres hver for seg. Bare utdanning etter fylte 18 år skal registreres.
    Dette gjelder også folkehøgskole og bibelskole.</p>
@if($hasErrors)
<div class="callout callout-danger bg-danger-subtle">
    Det er noen mangler i de registrerte opplysninger. Vennligst oppdater dem.
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
            <tbody class="table-group-divider">
                @foreach ($application->education as $id => $item)
                @if(request()->has('edit') && request()->edit == $id)
                <tr id="update">
                    <td colspan="10">
                        <form action="{{ route('update-single-education-information', ['edit' => $id]) }}" method="POST"
                            id="salary_form">
                            @csrf

                            <div class="row g-3 my-2 border border-primary border-2 bg-info p-2 p-md-4">
                                <h4 class="mb-4">Endre kompetanse</h4>
                                <div class="row">
                                    <!-- Topic and School -->
                                    <div class="col-6 col-md-3">
                                        <label for="update_topic_and_school" class="form-check-label">Studienavn og
                                            sted</label>
                                        <input type="text" id="update_topic_and_school" name="topic_and_school"
                                            value="{{ old('topic_and_school', $item['topic_and_school']) }}"
                                            class="form-control @error('topic_and_school') is-invalid @enderror">
                                        @error('topic_and_school')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="row justify-content-center">
                                            <!-- Start Date -->
                                            <div class="col-6 col-md-auto">
                                                <label for="update_start_date"
                                                    class="form-check-label">Studiestart</label>
                                                <input type="date" id="update_start_date" name="start_date"
                                                    min="1950-01-01"
                                                    value="{{ old('start_date', $item['start_date']) }}"
                                                    max="{{ date('Y-m-d') }}"
                                                    class="form-control @error('start_date') is-invalid @enderror"
                                                    style="max-width: 150px;">
                                                @error('start_date')
                                                <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- End Date -->
                                            <div class="col-6 col-md-auto">
                                                <label for="update_end_date"
                                                    class="form-check-label">Studieslutt</label>
                                                <input type="date" id="update_end_date" name="end_date" min="1950-01-01"
                                                    value="{{ old('end_date', $item['end_date']) }}"
                                                    class="form-control @error('end_date') is-invalid @enderror"
                                                    style="max-width: 150px;">
                                                @error('end_date')
                                                <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Study Points -->
                                    <div class="col-4 col-md-4 my-3">
                                        <input type="hidden" name="study_points" id="register_study_points"
                                            value="{{ old('study_points',$item['study_points']) }}">
                                        <label for="update_start_date" class="form-check-label">Studiepoeng</label>
                                        <input type="number" id="study_points_entry" name="study_points_entry"
                                            @if(old('study_points',$item['study_points'])=='bestått' )disabled @endif
                                            value="@if(old('study_points')!='bestått'){{ old('study_points',$item['study_points']) }}@endif"
                                            min="0" max="800"
                                            class="form-control @error('study_points') is-invalid @enderror"
                                            placeholder="@if(old('study_points',$item['study_points'])=='bestått')bestått @else Antall studiepoeng i tall. Eks 180 @endif"
                                            _="on keyup set #register_study_points.value to #study_points_entry.value">
                                        @error('study_points')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                        <div class="form-check form-switch my-2">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                @if(old('study_points',$item['study_points'])=='bestått' ) checked
                                                @endif id="register_studiepoeng"
                                                _="on change if my.checked then add @@disabled to #study_points_entry then set #register_study_points.value to 'bestått' then set #study_points_entry.placeholder to 'bestått' then set #study_points_entry.value to '' else remove @@disabled from #study_points_entry then set #register_study_points.value to #study_points_entry.value then set #study_points_entry.placeholder to 'Antall studiepoeng i tall. Eks 180' end">
                                            <label for="register_studiepoeng" class="form-check-label">
                                                Ikke studiepoeng/bestått
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Degree Section -->
                                    <div class="col-12 d-flex flex-wrap my-3">
                                        <div class="pe-4"><strong>Fullført grad:</strong></div>
                                        <div class="form-check pe-4">
                                            <input type="radio" id="update_no_degree" name="highereducation" value=""
                                                class="form-check-input @error('highereducation') is-invalid @enderror"
                                                @if(empty(old('highereducation', $item['highereducation'] ?? '' )))
                                                checked @endif>
                                            <label for="update_no_degree" class="form-check-label">Normalt studie, ingen
                                                grad</label>
                                        </div>
                                        <div class="form-check pe-4">
                                            <input type="radio" id="update_bachelor" name="highereducation"
                                                value="bachelor"
                                                class="form-check-input @error('highereducation') is-invalid @enderror"
                                                @if(old('highereducation', $item['highereducation'] ?? '' )==='bachelor'
                                                ) checked @endif>
                                            <label for="update_bachelor" class="form-check-label">Bachelorgrad eller
                                                Høgskolenivå (4 år)
                                            </label>
                                        </div>
                                        <div class="form-check pe-4">
                                            <input type="radio" id="update_master" name="highereducation" value="master"
                                                class="form-check-input @error('highereducation') is-invalid @enderror"
                                                @if(old('highereducation', $item['highereducation'] ?? '' )==='master' )
                                                checked @endif>
                                            <label for="update_master" class="form-check-label">Mastergrad,
                                                Sivilingeniør++</label>
                                        </div>
                                    </div>

                                    <!-- Relevance  -->
                                    <div class="col-12 d-flex flex-wrap my-3">
                                        <input type="hidden" name="relevance" value="false">
                                        <div class="form-check form-switch px-1">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="update_relevant" name="relevance" value="true" @if(old('relevance',
                                                $item['relevance'] ?? '' )==1) checked @endif>
                                            <label for="update_relevant" class="form-check-label">Særdeles høy relevanse
                                                for
                                                stillingen?</label>
                                        </div>
                                    </div>


                                    <!-- Submit Button -->
                                    <div class="col-12 d-flex flex-wrap align-items-center">
                                        <input type="submit" id="update-btn-update" name="submit"
                                            value="Oppdater utdanning"
                                            class="btn btn-primary me-2 @if(null === old('topic_and_school', $item['topic_and_school'])) disabled @endif">
                                        <a href="{{ route('enter-education-information') }}"
                                            class="btn btn-sm btn-outline-primary">Tilbake</a>
                                    </div>

                                </div>
                            </div>
                        </form>

                    </td>
                </tr>
                @else
                <tr>
                    <th id="topic_and_school-{{ $id }}" scope="row">{{ strlen($item['topic_and_school']) > 30 ?
                        substr($item['topic_and_school'], 0, 30) . '...' : $item['topic_and_school'] }}</th>
                    <td id="start_date-{{ $id }}">{{ $item['start_date'] }}</td>
                    <td id="end_date-{{ $id }}">{{ $item['end_date'] }}</td>
                    <td id="study_points-{{ $id }}">{{ $item['study_points'] }}</td>
                    <td id="percentage-{{ $id }}">{{ @$item['percentage'] }} {{ is_numeric($item['percentage']) ? '%' :
                        '' }}</td>
                    <td id="highereducation-{{ $id }}">{{ @$item['highereducation'] }}</td>
                    <td id="relevance-{{ $id }}">{{ @$item['relevance'] == true ? 'relevant' : '' }}</td>
                    <td>
                        <a class="btn btn-sm @if(in_array(null, [@$item['topic_and_school'], @$item['start_date'], @$item['end_date'], @$item['study_points'], @$item['percentage'], @$item['relevance']], true)) btn-danger @else btn-outline-primary @endif"
                            href="{{ route('enter-education-information', [$application, 'edit' => $id]) }}#update">
                            @if(in_array(null, [@$item['topic_and_school'], @$item['start_date'], @$item['end_date'],
                            @$item['study_points'], @$item['percentage'], @$item['relevance']], true))
                            Vennligst oppdater
                            @else
                            Endre
                            @endif
                        </a>
                    </td>

                    <td>
                        <a class=" btn btn-sm btn-outline-danger"
                            href="{{ route('destroy-education-information', ['id' => $id]) }}">
                            Slett linje
                        </a>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
        @endisset
        <div class="mt-5">
            <form action=" {{ route('post-education-information') }}" method="POST" id="salary_form">
                @csrf

                <div class="row g-3 mb-2 border border-primary border-2 bg-success-subtle p-2 p-md-4">
                    <h4 class="mb-4">Legg til kompetanse:</h4>
                    <!-- Topic and School -->
                    <div class="col-6 col-md-3">
                        <label for="topic_and_school" class="form-check-label">Studienavn og sted</label>
                        <input type="text" id="topic_and_school" name="topic_and_school"
                            value="{{ old('topic_and_school') }}" placeholder="Navn på studiet og studiested"
                            _="on keyup if my.value is not empty add .disabled to #btn-next then remove .disabled from #btn-submit else remove .disabled from #btn-next then add .disabled to #btn-submit end"
                            class="form-control @error('topic_and_school') is-invalid @enderror">
                        @error('topic_and_school')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="row justify-content-center">
                            <!-- Start Date -->
                            <div class="col-6 col-md-auto">
                                <label for="update_start_date" class="form-check-label">Studiestart</label>
                                <input type="date" id="start_date" name="start_date" min="1900-01-01"
                                    value="{{ old('start_date') }}" max="{{ date('Y-m-d') }}"
                                    class="form-control @error('start_date') is-invalid @enderror">
                                @error('start_date')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- End Date -->
                            <div class="col-6 col-md-auto">
                                <label for="update_end_date" class="form-check-label">Studieslutt</label>
                                <input type="date" id="end_date" name="end_date" min="1900-01-01"
                                    value="{{ old('end_date') }}"
                                    class="form-control @error('end_date') is-invalid @enderror">
                                @error('end_date')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- Study Points -->
                    <div class="col-4 col-md-4">
                        <input type="hidden" name="study_points" id="register_study_points"
                            value="{{ old('study_points') }}">
                        <label for="update_start_date" class="form-check-label">Studiepoeng</label>
                        <input type="number" id="study_points_entry" name="study_points_entry"
                            value="@if(old('study_points')!='bestått'){{ old('study_points') }}@endif" min="1" max="800"
                            @if(old('study_points')=='bestått' )disabled @endif
                            class="form-control @error('study_points') is-invalid @enderror"
                            placeholder="@if(old('study_points')=='bestått')bestått @else Antall studiepoeng i tall. Eks 180 @endif"
                            _="on keyup set #register_study_points.value to #study_points_entry.value">
                        @error('study_points')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <div class="form-check form-switch px-1 my-2">
                            <input class="form-check-input" type="checkbox" role="switch"
                                @if(old('study_points')=='bestått' ) checked @endif id="register_studiepoeng"
                                _="on change if my.checked then add @@disabled to #study_points_entry then set #register_study_points.value to 'bestått' then set #study_points_entry.placeholder to 'bestått' then set #study_points_entry.value to '' else remove @@disabled from #study_points_entry then set #register_study_points.value to #study_points_entry.value then set #study_points_entry.placeholder to 'Antall studiepoeng i tall. Eks 180' end">
                            <label for="register_studiepoeng" class="form-check-label">
                                Ikke studiepoeng/bestått
                            </label>
                        </div>
                    </div>

                    <!-- Degree Section -->
                    <div class="col-12 d-flex flex-wrap">
                        <div class="pe-4"><strong>Fullført grad:</strong></div>
                        <div class="form-check pe-4">
                            <input type="radio" class="form-check-input @error('highereducation') is-invalid @enderror"
                                id="no_degree" name="highereducation" @if(!old('highereducation')) checked @endif
                                value="">
                            <label class="form-check-label" for="no_degree">Uten grad</label>
                        </div>
                        <div class="form-check pe-4">
                            <input type="radio" class="form-check-input @error('highereducation') is-invalid @enderror"
                                id="bachelor" name="highereducation" value="bachelor"
                                @if(old('highereducation')==='bachelor' ) checked @endif>
                            <label class="form-check-label" for="bachelor">Bachelorgrad eller Høgskolenivå (4
                                år)</label>
                        </div>
                        <div class="form-check pe-4">
                            <input type="radio" class="form-check-input @error('highereducation') is-invalid @enderror"
                                id="master" name="highereducation" value="master" @if(old('highereducation')==='master'
                                ) checked @endif>
                            <label class="form-check-label" for="master">Mastergrad, Sivilingeniør++</label>
                        </div>
                    </div>

                    <!-- Relevance  -->
                    <div class="col-12 d-flex flex-wrap">
                        <input type="hidden" name="relevance" value="false">
                        <div class="form-check form-switch px-1 my-2">
                            <input type="checkbox" role="switch" class="form-check-input" id="relevant" name="relevance"
                                value="true">
                            <label class="form-check-label" for="relevant">Særdeles høy relevanse for
                                stillingen?</label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12 d-flex flex-wrap align-items-center">
                        <input type="submit"
                            class="form-control-input btn btn-sm btn-primary @if(null === old('topic_and_school')) disabled @endif"
                            id="btn-submit" name="submit" value="Registrer utdanning">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="text-md-end text-center pb-1">
    <a href="{{ route('enter-employment-information', $application) }}" class="btn btn-outline-primary my-2"
        tabindex="99">
        Forrige: Informasjon om stillingen
    </a>
    @if($hasErrors)
    <a href="{{ route('enter-experience-information', $application) }}" class="btn btn-primary disabled my-2"
        id="btn-next">
        Neste: Din ansiennitet
    </a><br />
    <span class="badge text-bg-danger">Du må oppdatere felt med mangler før du kan gå videre</span>
    @else
    <a href="{{ route('enter-experience-information', $application) }}" class="btn btn-primary my-2" id="btn-next">
        Neste: Din ansiennitet
    </a>
    @endif
</div>

@endsection
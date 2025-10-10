@extends('layouts.app')

@section('content')
    <div class="progress" role="progressbar" aria-label="Success example" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar bg-success" style="width: 65%">65%</div>
    </div>

    <h2>
        Kurs, verv og frivillig arbeid, relevant for stillingen.
    </h2>

    <div class="my-2 py-3">
        <p>
            Denne kalkulatoren kan ikke beregne frivillig arbeid automatsik. Normalt sett gies det bare ansiennitet eller
            kompetansetillegg i særtilfeller der det er brukt mye tid utover normal menighets- og organisasjonsliv. Er du
            eldste eller har vært kan du fører du det under arbeidserfaring. Det kan settes som en 25% stilling.
        </p>
    </div>
    @php
        $empoyeeCV = new \app\Models\EmployeeCV();
        $salaryCategory = $empoyeeCV->getPositionsLaddersGroups()[$application->job_title];
    @endphp

    @if ($salaryCategory['ladder'] === 'A')
        <h2>
            Spesifikke regler for gruppe {{ $salaryCategory['ladder'] . $salaryCategory['group'] }}
        </h2>
        <p>
            I menigheter som har et lederteam, der andre pastorer enn forstander bærer ansvar utover det normale, kan arbeidsgiver innstille på et ansvarstillegg mellom 1-4 trinn. Innstillingen skal begrunnes, på bakgrunn av stillingens ansvar, oppgaver og kompleksitet.
        </p>
    @elseif($salaryCategory['ladder'] === 'B')
        <h2>
            Spesifikke regler for gruppe {{ $salaryCategory['ladder'] . $salaryCategory['group'] }}
        </h2>
        <p>
            Menighetene: Menighetsarbeidere med overordnet lederansvar i menighetene kan gis 2 lønnstrinn i ansvarstillegg. Vurderingen av om menighetsarbeideren skal anses å ha overordnet lederansvar i menigheten foretas av lønnsutvalget eller den lønnsutvalget bemyndiger. Arbeidsgiver skal innlevere stillingsbeskrivelse som viser hvilket ansvar menighetsarbeideren har før ansvarstillegget vurderes.
        </p>
    @elseif($salaryCategory['ladder'] === 'D')
        <h2>
            Spesifikke regler for gruppe {{ $salaryCategory['ladder'] . $salaryCategory['group'] }}
        </h2>
        <p>
            Ektepar som er utsendinger og ansatt i 75% stilling, gis et lønnstrinn hver i ansvarstillegg, se pkt. 9.9.4 Det gis tillegg for feltansvar med inntil 3 lønnstrinn. Frikirkens hovedkontor gir innstilling om feltansvar.
        </p>
    @elseif($salaryCategory['ladder'] === 'E')
        <h2>
            Spesifikke regler for gruppe {{ $salaryCategory['ladder'] . $salaryCategory['group'] }}
        </h2>
        <p>
            Hovedkontoret: Leder for misjon gis 2 ekstra lønnstrinn i ansvarstillegg. Stabsleder gis 2 ekstra lønnstrinn i ansvarstillegg.
        </p>
    @elseif($salaryCategory['ladder'] === 'F')
        <h2>
            Spesifikke regler for gruppe {{ $salaryCategory['ladder'] . $salaryCategory['group'] }}
        </h2>
        <p>
            I menigheter som har et lederteam, hvor administrasjonsleder er en del av dette og bærer ansvar utover det en normale, kan arbeidsgiver innstille på et ansvarstillegg mellom 1-4 trinn. Innstillingen skal begrunnes, på bakgrunn av stillingens ansvar, oppgaver og kompleksitet.
        </p>
    @endif

    <div class="text-md-end text-center pb-1">
        <a href={{ route('enter-experience-information', $application) }} class="btn btn-outline-primary my-2 @if ($application->isReadOnly()) disabled @endif" tabindex="99" _="on click put 'Vennligst vent...' into my.innerHTML then wait 20ms then add .disabled to me end">
            Forrige side
        </a>
        <a href="{{ route('preview-and-estimated-salary', $application) }}" class="btn btn-primary my-2" id="btn-next" _="on click put 'Vennligst vent...' into my.innerHTML then wait 20ms then add .disabled to me end">
            Se Estimert<br />Lønnsplassering </a>
    </div>
@endsection

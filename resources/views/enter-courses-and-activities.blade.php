@extends('layouts.app')

@section('content')

<h2>
    Kurs, verv og frivillig arbeid, relevant for stillingen.
</h2>
<div class="progress" role="progressbar" aria-label="Success example" aria-valuenow="25" aria-valuemin="0"
    aria-valuemax="100">
    <div class="progress-bar bg-success" style="width: 80%">80%</div>
</div>


<div class="my-2 py-3">

    <p>Denne kalkulatoren kan ikke beregne frivillig arbeid automatsik. Normalt sett gies det bare ansiennitet eller
        kompetansetillegg i særtilfeller der det er brukt mye tid utover normal menighets- og organisasjonsliv. Er du
        eldste eller har vært kan du fører du det under arbeidserfaring. Det kan settes som en 25% stilling.</p>
</div>
<div class="text-md-end text-center pb-1">
    <a href={{ route('enter-experience-information', $application) }} class="btn btn-outline-primary my-2"
        tabindex="99">
        Forrige side
    </a>
    <a href="{{ route('preview-and-estimated-salary', $application) }}" class="btn btn-primary my-2" id="btn-next">
        Se Estimert<br />Lønnsplassering </a>

</div>
@endsection
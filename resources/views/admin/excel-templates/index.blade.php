@extends('layouts.app') {{-- Or your admin layout e.g., admin.layouts.app --}}

@section('content')
    <div class="container">
        <div class="row mb-3">
            <div class="col">
                <h1>Administrere Excel Maler</h1>
            </div>
        </div>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Får å sende excel skjemaer til brukere, må 3 utgaver av lønnsskjemaet (excel fil) være lagt inn i systemet. Det er originalen som benyttes internt hos Frikirken, og to andre dersom det er behov for flere rader i excel arket under ansiennitetsopplysninger.
            <br>
            <br>
            Dersom dette ser komplisert ut, ta kontakt med utvikler.
            <br>
            <br>
            Under ser du at du kan laste ned malene som benyttes i webappen. Og du har mulighet for å laste opp nye. Dette skjer gjerne når ny lønnsstige er vedtatt og justert. Det er viktig å bemerke seg at de utvidede malene må følge bestemte regler for at webappen skal kunne generere excel skjemaer. Derfor anbefales det å benytte de som allerede er lagt inn i systemet og endre disse.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @foreach ($templates as $template)
            @php $errorBagName = 'upload_' . str_replace('.', '_', $template['name']); @endphp
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Excel mal: {{ $template['name'] }}</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6>Viktig Informasjon:</h6>
                            <p class="text-muted small">{{ $template['help'] }}</p>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6>Last ned den gjeldende malen</h6>
                            @if ($template['exists'])
                                <a href="{{ route('admin.excel-templates.download', ['templateName' => $template['name']]) }}" class="btn btn-info">
                                    <i class="bi bi-download"></i> Last ned {{ $template['name'] }}
                                </a>
                            @else
                                <p class="text-muted">Malen eksisterer ikke.</p>
                                <button class="btn btn-info" disabled><i class="bi bi-download"></i> Last ned {{ $template['name'] }}</button>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6>Skriv over den gjeldende Malen for "{{ $template['name'] }}"</h6>
                            <form action="{{ route('admin.excel-templates.upload', ['templateName' => $template['name']]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <label for="template_file_{{ $loop->index }}" class="form-label visually-hidden">Last opp ny .xlsx fil for {{ $template['name'] }}:</label>
                                    <input type="file" class="form-control @if ($errors->$errorBagName->has('template_file')) is-invalid @endif" id="template_file_{{ $loop->index }}" name="template_file" accept=".xlsx" required>
                                    @if ($errors->$errorBagName->has('template_file'))
                                        <div class="invalid-feedback">{{ $errors->$errorBagName->first('template_file') }}</div>
                                    @endif
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Last opp & Skriv over</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

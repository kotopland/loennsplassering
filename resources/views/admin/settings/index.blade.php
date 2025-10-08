@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Innstillinger</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="report_email" class="form-label">Epostadresse for mottak av genererte lønnsskjemaer</label>
                <input type="email" class="form-control" id="report_email" name="report_email" value="{{ old('report_email', $settings['report_email'] ?? '') }}" required aria-describedby="emailHelp">
                {{-- <div id="emailHelp" class="form-text">Denne eposten vil innsendte lønnsskjemaer bli sent inn til.</div> --}}
                @error('report_email')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
@endsection

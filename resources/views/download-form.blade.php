@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Last ned lønnsskjema</div>

                    <div class="card-body">
                        <p>For å laste ned filen, vennligst bekreft din identitet ved å oppgi din fødselsdato og postnummer.</p>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('download-file', $application) }}">
                            @csrf

                            <div class="mb-3">
                                <label for="birth_date" class="form-label">Fødselsdato</label>
                                <input type="date" class="form-control" id="birth_date" name="birth_date" value="{{ old('birth_date') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="postal_code" class="form-label">Postnummer</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Last ned fil</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

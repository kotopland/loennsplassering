@extends('layouts.app')

@section('content')
    @if (session()->has('message'))
        <p class="alert my-2 {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
    @else
        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf
            <div>Du logger inn i admin verktøyet ved å fylle inn din e-postadresse og klikke "Send login link". E-postadressen må være registrert som administrator i verktøyet.</div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" required>
                @error('email')
                    <span>{{ $message }}</span>
                @enderror
            </div>

            <div>
                <button type="submit" _="on click wait 500ms then if #loginForm.checkValidity() then set my.innerHTML to 'Sending login link...' then add @@disabled to me" class="btn btn-primary">Send Login Link</button>
            </div>
        </form>
    @endif
@endsection

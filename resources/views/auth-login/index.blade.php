@extends('layouts.app')

@section('content')

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" required>
        @error('email')
        <span>{{ $message }}</span>
        @enderror
    </div>

    <div>
        <button type="submit" class="btn btn-primary">Send Login Link</button>
    </div>
</form>
@endsection
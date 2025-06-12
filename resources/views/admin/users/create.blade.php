@extends('layouts.app') {{-- Or your admin layout e.g., admin.layouts.app --}}

@section('content')
    <div class="container">
        <h1>Opprett Ny Bruker</h1>

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Navn</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Epost adresse</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{--
        Password field is intentionally omitted as the primary login is token-based.
        A random password will be set in the controller.
        If you need admins to set passwords, you can add a password field here:
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        --}}

            <button type="submit" class="btn btn-primary">Opprett Bruker</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Avbryt</a>
        </form>
    </div>
@endsection

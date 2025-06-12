@extends('layouts.app') {{-- Or your admin layout e.g., admin.layouts.app --}}

@section('content')
    <div class="container">
        <div class="row mb-3">
            <div class="col">
                <h1>Administrer Brukere</h1>
            </div>
            <div class="col text-end">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Opprett Ny Admin Bruker</a>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-auto">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Her kan du administrere brukere. Alle på listen er admin brukere. Når du har lagt inn en, kan de logge på ved å gå til <a href="{{ route('login') }}">Innloggingssiden</a> og de vil se hva du ser her.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Navn</th>
                    <th>E-post</th>
                    <th>Opprettet</th>
                    <th>Valg</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->created_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            @if (Auth::id() !== $user->id)
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Er du sikker på at du vil slette denne brukeren?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Slett</button>
                                </form>
                            @else
                                <button type="button" class="btn btn-danger btn-sm" disabled>Slett</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Ingen brukere funnet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

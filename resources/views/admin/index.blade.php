@extends('layouts.app')

@section('content')
    <h2>Administrasjon</h2>

    <div class="mb-5">

        <a href="{{ route('admin.employee-cv.index') }}" class="btn btn-outline-primary m-2">Lønnskjemaer</a><br />
        <a href="{{ route('admin.positions.index') }}" class="btn btn-outline-primary m-2">Stillinger</a><br />
        <br />
        <a href="{{ route('admin.salary-ladders.index') }}" class="btn btn-outline-primary m-2">Lønnsstiger</a><br />
        <a href="{{ route('admin.excel-templates.index') }}" class="btn btn-outline-primary m-2">Lønnskjema Maler</a><br />
        <br />
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary m-2">Admin Brukere</a><br />
        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-primary m-2">Admin E-post</a><br />
        <br />
        <a href="{{ route('admin.readme.show') }}" class="btn btn-outline-primary m-2">Om webappen</a><br />
        <br />
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-secondary m-2">Logg ut av admin verktøyet</button>
        </form>
    </div>
@endsection

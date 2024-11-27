@extends('layouts.app')

@section('content')

<h2>Administrasjon</h2>

<div class="mb-5">
    <a href="{{ route('admin.salary-ladders.index') }}" class="btn btn-primary">Lønnsstiger</a>
    <a href="{{ route('admin.positions.index') }}" class="btn btn-primary">Stillingstitler</a>
</div>
@endsection
@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Project README.md</h2>

        <div class="card">
            <div class="card-body">
                {!! $content !!}
            </div>
        </div>

    </div>
@endsection

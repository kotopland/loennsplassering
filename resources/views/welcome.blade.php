<!DOCTYPE html>
<html>

<head>
    <title>Lønnsplassering</title>
    <script src="https://unpkg.com/hyperscript.org@0.9.11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>

<body>
    <div class="container">
        @if (session()->has('message'))
            <p class="alert {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
        @endif

        <h1>Lønnsplassering</h1>
        @if (session('applicationId'))
            Hent din siste registrete lønnsplasseringsskjema
        @else
            <a href="{{ route('enter-employment-information') }}">Gjør en beregning av lønnen din</a>
        @endif
        <h2>Last opp et utfylt lønnsplasseringsskjema</h2>
        <form action="{{ route('loadExcel') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="excel_file" required>
            <button type="submit">Last inn og fortsett utfylling</button>
        </form>
    </div>
</body>

</html>

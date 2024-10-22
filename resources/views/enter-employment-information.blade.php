<!DOCTYPE html>
<html>

<head>
    <title>Lønnsplassering</title>
    <script src="https://unpkg.com/hyperscript.org@0.9.11"></script>
    <script src="https://unpkg.com/htmx.org@2.0.3"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {


            document.body.addEventListener('htmx:configRequest', (event) => {
                event.detail.headers['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
            })

        });
    </script>
</head>

<body>
    {{ session('applicationId') }}
    <div class="container">
        @if (session()->has('message'))
            <p class="alert {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
        @endif
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-6">
                    <h1>Lønnsplassering</h1>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <div>Ikke mist skjemaet. Send en <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#yourModal">lenke til dette skjemaet.</a></div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="yourModal" tabindex="-1" aria-labelledby="yourModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">

                        <h1 class="modal-title fs-5" id="yourModalLabel">Modal title</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Du kan enten, <a href="{{ route('open-application', session('applicationId')) }}">lagre denne lenken</a> eller få lenken sendt til din e-post adresse.

                        <div class="input-group">
                            <label for="email" class="form-label">Epost addresse</label>
                            <input type="email" class="form-control" id="email" name="email_address" aria-describedby="emailHelp">
                            <div id="emailHelp" class="form-text">Din adressen blir ikke lagret.</div>
                        </div>
                        <div class="pt-2 ps-2" id="email-result"></div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" hx-post="{{ route('send-application-link-to-email', session('applicationId')) }}" hx-trigger="click" hx-include="[name='email_address']" hx-target="#email-result">Send</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <form action="{{ route('post-employment-information') }}" method="POST" id="salary_form">
            @csrf
            <div class="my-2 py-3">
                <h2>Informasjon om stillingen</h2>
                <label class="form-label" for="job_title">Job Title:</label>
                <select class="form-control" name="job_title" id="job_title">
                    @foreach ($positionsLaddersGroups as $position => $positionArray)
                        <option value="{{ $position }}" @if (old('job_title', $employeeCV->job_title) === $position) selected @endif>{{ $position }}</option>
                    @endforeach
                </select>
                <div>
                    <label class="form-label" for="birth_date">Fødselsdato:</label>
                    <input type="date" class="form-control" name="birth_date" id="birth_date" required value="{{ old('birth_date', $employeeCV['birth_date']) }}">
                </div>
                <div>
                    <label class="form-label" for="birth_date">Starter i stillingen fra:</label>
                    <input type="date" class="form-control" name="work_start_date" id="work_start_date" required value="{{ old('work_start_date', $employeeCV['work_start_date']) }}">
                </div>
            </div>

            <button type="submit" class="btn btn-success">Neste: Din utdanning</button>
        </form>
    </div>
</body>

</html>

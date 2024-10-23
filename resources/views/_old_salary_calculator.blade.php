<!DOCTYPE html>
<html>

<head>
    <title>Lønnsplassering</title>
    <script src="https://unpkg.com/hyperscript.org@0.9.11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>

<body _="on init set $educationItems to 0">
    <div class="container">
        @if (session()->has('message'))
            <p class="alert {{ session()->get('alert-class', 'alert-info') }}">{{ session()->get('message') }}</p>
        @endif

        <h1>Lønnsplassering</h1>

        <form action="{{ route('salary.calculate') }}" method="POST" id="salary_form">
            @csrf
            <div class="my-2 py-3">
                <h2>Informasjon om stillingen</h2>
                <label class="form-label" for="job_title">Job Title:</label>
                <select class="form-control" name="job_title" id="job_title">
                    <option value="Pastor">Pastor</option>
                    <option value="Menighetsarbeider">Menighetsarbeider</option>
                </select>

                <label class="form-label" for="birth_date">Fødselsdato:</label>
                <input type="date" class="form-control" name="birth_date" id="birth_date" required value="2000-04-02">
            </div>

            <div class="my-2 py-3">
                <h2>Utdanning</h2>
                <div id="education_entries">
                </div>

                <template id="education_template">
                    <div class="row g-3 mb-2 border border-1 m-2 p-2">
                        <div class="col-auto">
                            <input id="title" type="text" name="education[title][]" placeholder="Title">
                            <input type="date" name="education[start_date][]">
                            <input type="date" name="education[end_date][]">
                        </div>
                        <div class="row g-3 mb-2">
                            <div class="col-auto pe-4">
                                <select name="education[study_points][]">
                                    <option>Velg fra listen</option>
                                    <option value="0">Bestått</option>
                                    <option value="30">30</option>
                                    <option value="60">60</option>
                                    <option value="120">120</option>
                                    <option value="180">180</option>
                                    <option value="240">240</option>
                                    <option value="300">300</option>
                                </select>
                            </div>
                            <div class="col-auto pe-4">
                                <input type="radio" class="form-check-input" name="education[highereducation][]" checked value="">
                                <label class="form-check-label" for="bachelor">Uten grad</label>
                            </div>
                            <div class="col-auto pe-4">
                                <input type="radio" class="form-check-input" name="education[highereducation][]" value="bachelor">
                                <label class="form-check-label" for="bachelor">Bachelor</label>
                            </div>
                            <div class="col-auto pe-4">
                                <input type="radio" class="form-check-input" name="education[highereducation][]" value="master">
                                <label class="form-check-label" for="master">Master/PhD?</label>
                            </div>
                            <div class="col-auto">
                                <input type="checkbox" class="form-check-input" id="relevant" name="education[master_or_phd][]" value="1">
                                <label class="form-check-label" for="relevant">Særdeles høy relevanse for stillingen?</label>
                                <button class="btn btn-sm btn-secondary" type="button" _="on click put #education_template's innerHTML before me">Fjern linje</button>
                            </div>
                        </div>
                    </div>
                </template>

                <button class="btn btn-sm btn-primary" type="button" _="on click put #education_template's innerHTML before me">Leggg til mer kompetanse</button>
            </div>
            <div class="my-2 py-3">

                <h2>Ansiennitet</h2>
                <div id="work_experience_entries">
                </div>
                <button type="button" class="btn btn-sm btn-primary">Add Work Experience</button>

                <template id="work_experience_template">
                    <div>
                        <input type="text" name="work_experience[][title]" placeholder="Title">
                        <input type="text" name="work_experience[][work_place]" placeholder="Work Place">
                        <input type="checkbox" name="work_experience[][religious]" value="1"> Religious Organization
                        <input type="number" name="work_experience[][percentage]" min="10" max="100" placeholder="Percentage">
                        <input type="date" name="work_experience[][start_date]">
                        <input type="date" name="work_experience[][end_date]">
                    </div>
                </template>
            </div>

            <div class="my-2 py-3">

                <h2>Kurs, verv og frivillig arbeid, relevant for stillingen. </h2>
                <p>Opplysninger her kan ikke beregnes automatisk. Normalt sett gies det bare ansiennitet eller kompetansetillegg i særtilfeller der det er brukt mye tid utover normal menighets- og organisasjonsliv.</p>
            </div>
            <button type="submit" class="btn btn-success">Preview</button>
        </form>
    </div>
</body>

</html>

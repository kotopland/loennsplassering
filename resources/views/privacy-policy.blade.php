@extends('layouts.app')

@section('content')
    <a href="javascript:history.back()" class="btn btn-primary">Tilbake</a>
    <h1>Personvernerklæring for Frikirkens lønnsberegner</h1>
    <p><strong>Sist oppdatert:</strong> 26. oktober 2024</p>

    <h2>1. Hvilke personopplysninger behandles?</h2>
    <p>Frikirkens lønnsberegner behandler ulike typer data avhengig av hvor du er i prosessen:</p>
    <ul>
        <li><strong>Før innsending:</strong> Frem til du aktivt velger å sende inn skjemaet for behandling, lagrer vi kun anonymiserte data knyttet til din økt. Dette inkluderer fødselsdato, utdanning og arbeidserfaring for å kunne utføre beregningene. Ingen personlig identifiserbar informasjon som navn, adresse eller e-post lagres på dette stadiet.</li>
        <li><strong>Ved innsending for behandling:</strong> Når du fyller ut personalia og trykker "Send inn for behandling", lagrer vi personopplysningene du oppgir. Dette inkluderer navn, kontaktinformasjon, adresse, informasjon om arbeidsgiver og overordnede.</li>
    </ul>

    <h2>2. Formålet med datainnsamlingen</h2>
    <p>Informasjonen du registrerer lagres i en database for følgende formål:</p>
    <ul>
        <li>For at lønnsutvalget og de de bemyndiger skal kunne behandle din lønnsplassering.</li>
        <li>For at du skal kunne hente frem igjen skjemaet ditt ved en senere anledning.</li>
        <li>For at utviklere skal kunne forbedre og finjustere verktøyet.</li>
    </ul>

    <h2>3. Informasjonskapsler (Cookies)</h2>
    <p>Nettstedet bruker <strong>informasjonskapsler</strong> for å:</p>
    <ul>
        <li>Forbedre brukeropplevelsen.</li>
        <li>Lagre brukerens valg av samtykke til cookies.</li>
    </ul>
    <p>Informasjonskapslene inneholder ingen personlige opplysninger.</p>

    <h2>4. Samtykke til informasjonskapsler</h2>
    <p>Ved første besøk vil du bli presentert med en cookie-banner for å gi samtykke til bruken av cookies. Du kan når som helst endre eller trekke tilbake ditt samtykke ved å justere innstillingene i nettleseren.</p>

    <h2>5. Dine rettigheter</h2>
    <p>I henhold til <strong>GDPR</strong> har du rett til:</p>
    <ul>
        <li>Innsyn i hvilke opplysninger som er lagret.</li>
        <li>Retting eller sletting av opplysninger.</li>
        <li>Å klage til Datatilsynet hvis du mener personvernreglene er brutt.</li>
    </ul>

    <h2>6. Kontaktinformasjon</h2>
    <p>For spørsmål om personvern kan du kontakte oss på <strong>www.frikirken.no</strong>.</p>
@endsection

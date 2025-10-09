# Frikirkens Lønnsberegner

## Beskrivelse
Frikirkens lønnsberegner er et verktøy som estimerer lønnsplassering basert på ansiennitet, kompetansepoeng og den vedtatte lønnstabell. Det hjelper ansatte med å få en sannsynlig lønnsplassering etter ansettelse, basert på synodestyrets lønnsavtale.

## Funksjonalitet
*   **Steg-for-steg datainnsamling:** Brukere kan enkelt legge inn informasjon om utdanning, arbeidserfaring, kurs og andre aktiviteter gjennom et guidet forløp.
*   **Beregning av ansiennitet og kompetansepoeng:** Systemet analyserer og justerer innsendt data for å beregne ansiennitet og kompetansepoeng i henhold til gjeldende regler.
*   **Estimering av lønnsplassering:** Gir et estimat på lønnstrinn basert på de beregnede poengene og ansienniteten.
*   **Tidslinjevisualisering:** Viser en oversiktlig tidslinje for utdanning og arbeidserfaring.
*   **Lagre og fortsett senere:** Brukere kan få tilsendt en lenke på e-post for å fortsette utfyllingen på et senere tidspunkt.
*   **Excel-eksport:** Genererer et Excel-dokument med den beregnede lønnsplasseringen og detaljert grunnlag.
*   **Opplasting av eksisterende skjema:** Mulighet for å laste opp et tidligere utfylt Excel-skjema for å fortsette arbeidet.
*   **Administrasjonsgrensesnitt:** Et eget panel for administratorer for å håndtere innsendte søknader, brukere, stillinger, lønnsstiger og maler.

## Begrensninger

- Verktøyet tar ikke hensyn til arbeidsgivers vurderinger, noe som kan føre til avvik på 1-2 lønnstrinn.
- Verktøyet tar heller ikke hensyn til de som er allerede i toppen av lønnsstigen og kan få erfaringsbasert kompetansetillegg ofte gitt pr ytterligere 5 år, noe som kan føre til avvik på 1-5 lønnstrinn.
- Verktøyet tar ikke hensyn til frivillig verv som kan benyttes i ulike tilfeller.
## Håndtering av Data
*   **E-postadresser:** Brukes for å sende engangslenker for å gjenoppta en økt. Adressene lagres kun midlertidig.
*   **Personopplysninger:** Nødvendig personinformasjon lagres for å utføre beregningene.
*   **Sletting:** Data knyttet til en økt slettes automatisk etter en viss tid.
---
## Detaljert Beregningsgrunnlag

Lønnsberegneren følger et sett med regler for å justere og vurdere utdanning og arbeidserfaring for å komme frem til et estimat for lønnsplassering. Her er en oversikt over nøkkelprosessene:

### 1. Justering av Utdanning

*   **Aldersgrense**: Utdanning starter normalt å telle fra fylte 18 år.
    *   Utdanning som er fullført før fylte 18 år, fjernes vanligvis.
    *   For utdanning som starter før og avsluttes etter 18-årsdagen (unntatt VGS/fagskole), justeres startdatoen til 18-årsdagen. For VGS/fagskole beholdes opprinnelig startdato, men perioden før 18 år vil ikke telle med i ansiennitetsberegning dersom utdanningen senere konverteres.
*   **Studieprosent**: En studieprosent beregnes basert på normert tid, faktisk tid og antall studiepoeng. Denne rundes opp til nærmeste 10%.
*   **Kompetansepoeng**:
    *   Det beregnes kompetansepoeng for hver utdanningsenhet basert på type (bachelor, master, cand.theol., etc.), studiepoeng (eller "bestått") og relevans.
    *   For "bestått" uten spesifiserte studiepoeng, gis 1 poeng hvis utdanningen er relevant og har vart i minst 9 måneder.
    *   Ufullstendige, relevante grader (minst 60 studiepoeng, men ikke "bestått" og ikke definert som høyere utdanning) kan gi poeng, men er begrenset til maksimalt 2 år totalt for slike tilfeller.
*   **Tak for Kompetansepoeng**: Det totale antallet kompetansepoeng begrenses (cappes) basert på den ansattes stillingsstige (A-F) og gruppe (for stige C):
    *   Stige A, B, E, F: Maks 7 poeng.
    *   Stige C (gruppe 2): Maks 5 poeng.
    *   Stige C (gruppe 1): Maks 2 poeng.
    *   Stige D: Maks 4 poeng.
*   **Overføring til Arbeidserfaring**:
    *   Utdanning som gir 0 kompetansepoeng, konverteres til arbeidserfaring.
    *   Hvis totalt antall kompetansepoeng overstiger taket for stillingsstigen, vil utdanning med lavest prioritet (basert på poeng, relevans, og studiepoeng) konverteres til arbeidserfaring inntil poengsummen er innenfor taket.
*   **Datojustering**: For å sikre korrekte periodeberegninger, justeres sluttdatoer for utdanning som er satt til den første dagen i en måned, til siste dag i foregående måned.

### 2. Justering av Arbeidserfaring

*   **Aldersgrense**:
    *   Arbeidserfaring som er avsluttet før fylte 18 år, fjernes.
    *   Hvis en arbeidsperiode starter før 18-årsdagen og avsluttes etter, justeres startdatoen til dagen etter 18-årsdagen.
*   **Begrensning mot Tiltredelsesdato**: Arbeidserfaring justeres slik at den ikke går utover tiltredelsesdato for den nye stillingen. Sluttdato settes til dagen før tiltredelsesdato.
*   **Overlapp med Utdanning**:
    *   Arbeidserfaring som overlapper med kompetansegivende utdanning, splittes opp for å unngå samtidig uttelling, med mindre spesifikke unntak gjelder (f.eks. relevant høyere utdanning etter 1. januar 2015 som overlapper med relevant arbeid i frikirke/annen kristen virksomhet, eller hvis utdanningen er konvertert til arbeidserfaring).
*   **Stillingsprosent og Prioritering ved Overlapp**:
    *   For "frikirkestillinger" etter 1. mai 2014 settes stillingsprosenten automatisk til 100% og erfaringen som relevant.
    *   Den totale registrerte arbeidsprosenten for en gitt tidsperiode kan ikke overstige 100%. Ved overlappende arbeidsforhold splittes periodene opp. Prosentandeler fordeles og eventuelt reduseres for å overholde 100%-regelen, hvor arbeidsforhold prioriteres basert på deres opprinnelige startdato.
    *   Etter justeringer slås sammenhengende perioder med lik tittel og (justert) prosentandel sammen.
*   **Fjerning av Duplikater**: Identiske arbeidserfaringsperioder (basert på tittel, startdato og sluttdato) fjernes.

### 3. Beregning av Ansiennitet (Total Arbeidserfaring)

*   Total ansiennitet beregnes i måneder basert på de justerte arbeidsperiodene (inkludert utdanning som er konvertert til arbeidserfaring).
*   Hver periode bidrar med `(antall måneder * stillingsprosent / 100)`.
*   Ikke-relevant arbeidserfaring teller 50% av tiden.
*   En spesiell metode for datodifferanse brukes for å etterligne Excels `DATEDIF(start;slutt;"M")` for månedsberegning.

### 4. Estimering av Lønnstrinn

*   **Ansiennitetsdato**: En beregnet ansiennitetsdato finnes ved å trekke den totale ansienniteten (i måneder) fra den oppgitte tiltredelsesdatoen for den nye stillingen.
*   **Lønnstrinn**: Lønnstrinnet estimeres som antall hele år fra den beregnede ansiennitetsdatoen frem til dagens dato.

### Oppsummering av Databehandling

1.  **Innhenting**: Fødselsdato, utdanning og arbeidserfaring legges inn av brukeren.
2.  **Aldersjustering**: Både utdanning og arbeidserfaring justeres i henhold til 18-årsgrensen.
3.  **Kompetansepoeng**: Poeng beregnes for utdanning og cappes. Overskytende/null-poengs utdanning konverteres til arbeidserfaring.
4.  **Overlappshåndtering**:
    *   Utdanningsperioder justeres (f.eks. sluttdatoer).
    *   Arbeidserfaringsperioder justeres for å unngå overlapp med utdanning (med visse unntak).
5.  **Konsolidering av Arbeidserfaring**:
    *   Stillingsprosent justeres (f.eks. "frikirkeregel").
    *   Total prosentandel per tidsenhet begrenses til 100%.
    *   Duplikater fjernes.
    *   Sammenhengende, like perioder slås sammen.
6.  **Total Ansiennitet**: Beregnes i måneder fra all gyldig og justert arbeidserfaring.
7.  **Lønnstrinn**: Estimeres basert på total ansiennitet og tiltredelsesdato.

Dette systematiske regelsettet sikrer en konsistent og etterprøvbar beregning av lønnsplassering i henhold til de definerte retningslinjene.

---

## Komme i Gang

1. **Start nytt skjema** fra forsiden.
2. Alternativt kan du **fortsette** med et tidligere skjema eller **laste opp et utfylt Excel-skjema**.

---

## Krav til System

- Webserver (f.eks nginx eller apache)
- PHP >= 8.2
- Laravel 12
- MariaDB / MySQL / PostgreSQL

---

## Installering

1.  **Klon prosjektet:**
    ```bash
   git clone <repo-url>
    cd prosjekt-mappe
    ```
2.  **Installer avhengigheter:**
    ```bash
    composer install
   npm install && npm run build
   ```
3.  **Konfigurer .env:**
    ```bash
    cp .env.example .env
   php artisan key:generate
   ```
4.  **Kjør migrasjoner og seeding:**
    ```bash
    php artisan migrate:fresh --seed
    ```
5.  **Sett opp adminbruker:**
    For å opprette en administrator, kan du kjøre følgende i `tinker`:
    ```php
    php artisan tinker
    \App\Models\User::create(['name' => 'Admin Name', 'email' => 'admin@example.com', 'password' => Illuminate\Support\Facades\Hash::make(Illuminate\Support\Str::random(16))]);
    ```

## Planlegging og Oppgaver
Oppgaver er definert i
```
routes/console.php
```

For å kjøre oppgavene manuelt:
```
php artisan schedule:run
```

Legg til en cron-jobb for å kjøre hvert minutt:
```
* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
```

## Lisens
Dette prosjektet er lisensiert under MIT-lisensen. Se LICENSE for detaljer.

## Kontakt
For spørsmål om personvern, kontakt oss på [www.frikirken.no](www.frikirken.no).
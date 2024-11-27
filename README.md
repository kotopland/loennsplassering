# Frikirkens Lønnsberegner

## Beskrivelse

Frikirkens lønnsberegner er et verktøy som estimerer lønnsplassering basert på ansiennitet, kompetansepoeng og statens lønnstabell. Det hjelper ansatte med å få en sannsynlig lønnsplassering etter ansettelse, basert på synodestyrets lønnsavtale.

## Funksjonalitet

- Beregner kompetansepoeng og ansiennitet.  
- Viser tidslinjer over kompetanse og ansiennitet.  
- Genererer et Excel-vedlegg med beregnet lønnsplassering.  
- Mulighet for å laste opp eksisterende lønnsskjema for videre arbeid.

## Begrensninger

Verktøyet tar ikke hensyn til arbeidsgivers vurderinger, noe som kan føre til avvik på 1-2 lønnstrinn.

## Håndtering av Data

- **E-postadresser** lagres kun midlertidig i en kø og slettes innen 2 minutter.
- Kun **fødselsdato** lagres midlertidig for beregningsformål.

---

## Komme i Gang

1. **Start nytt skjema** fra forsiden.
2. Alternativt kan du **fortsette** med et tidligere skjema eller **laste opp et utfylt Excel-skjema**.

---

## Krav til System

- PHP >= 8.1  
- Laravel 11  
- MySQL / PostgreSQL (valgfritt)  

---

## Installering

1. **Klon prosjektet:**
   ```
   bash
   git clone <repo-url>
   cd prosjekt-mappe
   ```
2. **Installer avhengigheter:**
   ```
   composer install
   npm install && npm run build
   ```
3. **Konfigurer .env:**
   ```
   cp .env.example .env
   php artisan key:generate
   ```
4. **Kjør migrasjoner:**
   ```
   php artisan migrate:fresh --seed
   ```

5. **Sett opp admin bruker:**
```
php artisan tinker
$user = User::insert(['name'=>'Your Name','email'=>'your.email@example.com', 'password'=>Hash::make(Str::random(10))]);
```
You can then access the admin pages on [https://app.domain/admin] (https://app.domain/admin)

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
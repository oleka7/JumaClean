# Juma Clean Form WordPress Plugin

En komplett WordPress plugin for Juma Clean bestillingsskjema med dynamisk prising, reCAPTCHA og stegvis navigasjon.

## Funksjoner

- ✅ **Stegvis skjema** - 4 steg med fremdriftsindikator
- ✅ **Dynamisk prising** - Automatisk beregning med rabatter
- ✅ **reCAPTCHA integrasjon** - Spam-beskyttelse
- ✅ **Google Sheets integrasjon** - Automatisk eksport av alle bestillinger til Google Sheets
- ✅ **Responsivt design** - Fungerer på alle enheter
- ✅ **E-post notifikasjoner** - Til admin og kunde
- ✅ **Utvidet admin dashboard** - Se alle bestillinger med fullstendige detaljer
- ✅ **Sikkerhet** - Input validering og sanitization
- ✅ **Custom post type** - Lagrer bestillinger i WordPress

## Installasjon

### 1. Last opp plugin
1. Pakk ut alle filene til en mappe: `juma-clean-form/`
2. Last opp mappen til `/wp-content/plugins/` på din WordPress server
3. Aktiver pluginen i WordPress admin under "Plugins"

### 2. Konfigurer reCAPTCHA
1. Gå til **Settings → Juma Clean** i WordPress admin
2. Legg inn din reCAPTCHA Site Key: `6LdB2_QrAAAAAFKcOvn8iEKLjHXILce8vvKF_Zuk`
3. Legg inn din reCAPTCHA Secret Key: `6LdB2_QrAAAAANeBR-bzF8LvwvUHKB_7Bj065Kez`
4. Klikk "Save Changes"

### 3. Konfigurer Google Sheets (valgfritt)
1. Åpne Google Sheets og opprett et nytt ark
2. Gå til **Extensions → Apps Script**
3. Åpne filen `google-apps-script.js` som følger med pluginen
4. Kopier hele innholdet og lim inn i Apps Script editoren
5. Klikk **Save** (💾)
6. Klikk **Deploy → New deployment**
7. Velg type: **Web app**
8. Execute as: **Me**
9. Who has access: **Anyone**
10. Klikk **Deploy**
11. Kopier **Web app URL** (ser ut som: `https://script.google.com/macros/s/...`)
12. Gå tilbake til WordPress → **Settings → Juma Clean**
13. Lim inn URL i **Google Sheets Web App URL** feltet
14. Klikk **Save Changes**

**Ferdig!** Alle nye bestillinger vil nå automatisk havne i Google Sheets.

### 4. Legg til skjema på side
1. Opprett en ny side i WordPress
2. Legg til shortcode: `[juma_clean_form]`
3. Publiser siden

## Bruk

### Shortcode
```
[juma_clean_form]
```

### Med alternativer
```
[juma_clean_form show_title="false"]
```

## Admin funksjoner

### Se bestillinger
- Gå til **Juma Bestillinger** i WordPress admin
- Se oversikt over alle bestillinger med:
  - Navn (fornavn + etternavn)
  - Kundetype (Privat/Næring)
  - Vaskepakke (Liten/Medium/Stor)
  - Full adresse (gatenavn, husnr, oppgang, postnr, poststed)
  - Kontaktinformasjon (telefon + e-post)
  - Dato for bestilling
- Klikk på en bestilling for å se alle detaljer i et oversiktlig format

### E-post notifikasjoner
- **Admin**: Mottar e-post ved ny bestilling
- **Kunde**: Mottar bekreftelse på e-post

### Google Sheets integrasjon
- Alle bestillinger sendes automatisk til Google Sheets (når konfigurert)
- Data inkluderer:
  - Tidspunkt for bestilling
  - Kundetype og vaskepakke
  - Fullstendig kontaktinformasjon
  - Full adresse
- Perfekt for å dele data med team eller lage rapporter

## Pakke typer

### Liten vaskepakke (19 vaskedager)
- Mat: 7 beholdere
- Rest: 3 beholdere  
- Glass: 3 beholdere
- Papir: 3 beholdere
- Plast: 3 beholdere

### Medium vaskepakke (22 vaskedager)
- Mat: 7 beholdere
- Rest: 5 beholdere
- Glass: 3 beholdere
- Papir: 4 beholdere
- Plast: 3 beholdere

### Stor vaskepakke (26 vaskedager)
- Mat: 10 beholdere
- Rest: 5 beholdere
- Glass: 3 beholdere
- Papir: 4 beholdere
- Plast: 4 beholdere

## Rabatt struktur

- 1 vaskedag = 0% rabatt
- 2-4 vaskedager = 10% rabatt
- 5-9 vaskedager = 20% rabatt
- 10-19 vaskedager = 30% rabatt
- 20-29 vaskedager = 40% rabatt
- 30-39 vaskedager = 50% rabatt
- 40+ vaskedager = 60% rabatt

## Priser

- Liten beholder: 179 kr per vaskedag
- Stor beholder: 279 kr per vaskedag

## Sikkerhet

- **reCAPTCHA v2** - Bot-beskyttelse
- **Input validering** - HTML5 og JavaScript
- **Data sanitization** - Renser alle input
- **Nonce verifisering** - CSRF-beskyttelse
- **SQL injection beskyttelse** - Prepared statements

## Tilpasning

### CSS
Rediger `assets/style.css` for å endre utseende.

### JavaScript
Rediger `assets/script.js` for å endre funksjonalitet.

### PHP
Rediger `juma-clean-form.php` for å endre backend-logikk.

## Feilsøking

### reCAPTCHA fungerer ikke
- Sjekk at Site Key og Secret Key er riktig
- Sjekk at domenet er registrert hos Google reCAPTCHA
- Sjekk at HTTPS er aktivert

### Skjema vises ikke
- Sjekk at pluginen er aktivert
- Sjekk at shortcode er riktig: `[juma_clean_form]`
- Sjekk for JavaScript-feil i nettleser

### E-post sendes ikke
- Sjekk WordPress e-post konfigurasjon
- Sjekk spam-mappe
- Test med e-post test plugin

### Google Sheets fungerer ikke
- Sjekk at Web App URL er riktig kopiert
- Sjekk at Apps Script er deployed som "Web app"
- Sjekk at "Who has access" er satt til "Anyone"
- Test Apps Script med testDoPost() funksjonen
- Sjekk at Google Sheet ikke er privat/låst

## Support

For support og spørsmål, kontakt utvikleren.

## Lisens

GPL v2 eller senere

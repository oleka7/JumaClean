# Juma Clean Form - Nye Funksjoner

## 📋 Oversikt over endringer

### ✅ 1. Google Sheets Integrasjon
**Hva gjør den?**
- Alle skjemainnleveringer sendes automatisk til Google Sheets
- Lik oppsett som reCAPTCHA - enkelt å konfigurere
- Data lagres i sanntid når noen sender inn skjema

**Hvordan sette det opp:**

#### Steg 1: Opprett Google Sheets Web App
1. Åpne Google Sheets og lag et nytt ark
2. Gå til **Extensions → Apps Script**
3. Åpne filen `google-apps-script.js` (ligger i plugin-mappen)
4. Kopier alt og lim inn i Apps Script editoren
5. Klikk **💾 Save**
6. Klikk **Deploy → New deployment**
7. Velg **⚙️ Settings icon** → **Web app**
8. **Execute as**: Me
9. **Who has access**: Anyone
10. Klikk **Deploy**
11. **Kopier Web app URL** (ser slik ut: `https://script.google.com/macros/s/AKfy...`)

#### Steg 2: Konfigurer i WordPress
1. Gå til WordPress admin → **Settings → Juma Clean**
2. Finn seksjonen **"Google Sheets Integrasjon"**
3. Lim inn Web App URL i feltet
4. Klikk **Save Changes**

**Ferdig!** 🎉 Alle nye bestillinger havner nå automatisk i Google Sheets.

---

### ✅ 2. Utvidet "Juma Bestillinger" Dashboard

**Hva er nytt?**
- Du ser nå **alle felter** i oversikten, ikke bare navn
- Nye kolonner:
  - ✅ **Kundetype** (Privat/Næring)
  - ✅ **Pakke** (Liten/Medium/Stor + antall dager)
  - ✅ **Adresse** (gatenavn, husnr, oppgang, postnr, poststed)
  - ✅ **Kontakt** (telefon + e-post med klikkbare lenker)
  - ✅ **Dato**

**Hvordan bruke det:**
1. Gå til **Juma Bestillinger** i WordPress admin
2. Se fullstendig oversikt i tabellen
3. Klikk på en bestilling for å se alle detaljer i en fin formatert boks

---

## 📁 Hvilke filer er endret?

### `juma-clean-form.php`
- ✅ Ny funksjon: `send_to_google_sheets()` - sender data til Google Sheets
- ✅ Ny funksjon: `set_custom_columns()` - definerer custom kolonner
- ✅ Ny funksjon: `custom_column_content()` - viser innhold i kolonnene
- ✅ Ny funksjon: `add_order_meta_boxes()` - legger til meta box
- ✅ Ny funksjon: `render_order_details_meta_box()` - viser alle detaljer
- ✅ Utvidet admin page med Google Sheets innstillinger

### `google-apps-script.js` (NY FIL)
- Google Apps Script kode for Google Sheets integrasjon
- Inkluderer test-funksjon
- Automatisk oppretter header-rad
- Formaterer data pent

### `README.md`
- Oppdatert med nye funksjoner
- Detaljerte instruksjoner for Google Sheets oppsett
- Feilsøkingstips

---

## 🎯 Fordeler med de nye funksjonene

### Google Sheets Integrasjon
- ✅ **Enkel deling**: Del Google Sheet med teamet ditt
- ✅ **Backup**: All data er også i skyen
- ✅ **Analyse**: Bruk Google Sheets verktøy for rapporter
- ✅ **Export**: Eksporter til Excel, CSV, etc.
- ✅ **Sanntid**: Data vises øyeblikkelig

### Utvidet Dashboard
- ✅ **Raskere oversikt**: Se all info uten å klikke
- ✅ **Bedre UX**: Klikk direkte på telefon/e-post
- ✅ **Profesjonelt**: Pent formatert med farger og ikoner
- ✅ **Effektivt**: Spar tid ved å se alt på ett sted

---

## 🔧 Tekniske detaljer

### Google Sheets Integration
- Bruker `wp_remote_post()` for sikker HTTP-kommunikasjon
- JSON data format
- 15 sekunders timeout
- Error logging hvis noe går galt
- Fungerer selv om Google Sheets er nede (lagres fortsatt i WordPress)

### Admin Dashboard
- Custom post type columns
- Meta boxes med styling
- Responsive design
- Emojis for bedre lesbarhet
- Klikkbare lenker (tel: og mailto:)

---

## 🚀 Komme i gang (Hurtigguide)

1. **Last opp oppdatert plugin** til WordPress
2. **Aktiver** pluginen (eller re-aktiver hvis den allerede var aktiv)
3. **Gå til Settings → Juma Clean**
4. **Se de nye feltene** under "Google Sheets Integrasjon"
5. **Følg instruksjonene** for å sette opp Google Sheets
6. **Gå til Juma Bestillinger** og se den nye oversikten!

---

## ❓ Vanlige spørsmål

### Q: Må jeg bruke Google Sheets?
**A:** Nei, det er valgfritt. Alt fungerer som før selv om du ikke konfigurerer det.

### Q: Koster Google Sheets noe?
**A:** Nei, Google Sheets er gratis.

### Q: Hva skjer med gamle bestillinger?
**A:** De vises med de nye kolonnene. Nye bestillinger sendes også til Google Sheets.

### Q: Kan jeg endre på Google Sheet strukturen?
**A:** Ja, men ikke slett eller omdøp kolonnene som Apps Script oppretter.

### Q: Fungerer det hvis flere sender skjema samtidig?
**A:** Ja, Google Apps Script håndterer flere samtidige requests.

---

## 📞 Support

Hvis du har spørsmål eller problemer:
1. Les README.md for detaljerte instruksjoner
2. Sjekk feilsøkingsseksjonen i README.md
3. Test Apps Script med testDoPost() funksjonen

---

### ✅ 3. Dynamisk prisadministrasjon

**Hva gjør den?**
- Alle priser kan nå redigeres fra WordPress admin uten å endre kode
- Priser lastes dynamisk i frontend - ingen caching-problemer
- Administrer enkeltvask priser, sesongvask priser og antall dager per pakke
- Endringer trer i kraft umiddelbart

**Slik bruker du det:**

#### Rediger priser i admin
1. Gå til **WordPress Admin → Settings → Juma Clean**
2. Finn seksjonen **"Prisinnstillinger"**
3. Rediger ønskede priser og klikk **"Save Changes"**

#### Hva kan redigeres:
- ✅ **Enkeltvask priser**: Liten og stor søppelbøtte
- ✅ **Sesongvask priser**: Daglige priser for liten og stor bøtte
- ✅ **Pakke-dager**: Antall vaskedager i hver sesongpakke (liten/medium/stor)

**Tekniske detaljer:**
- Priser lagres som WordPress options
- JavaScript laster priser via AJAX ved sidelasting
- Fallback til default-verdier hvis server ikke svarer
- Ingen database-endringer kreves

---

### ✅ 4. Forbedret skjema-navigasjon og validering

**Hva er nytt:**
- ✅ **Tilbake-knapper** i alle steg - brukere kan gå tilbake og rette feil
- ✅ **Obligatorisk valg av søppelbøtter** - "Fortsett" knappen er disabled/grået ut til minst 1 bøtte er valgt
- ✅ **Visuell feedback** - knappen fades ut når den ikke kan brukes, full farge når klar

**Slik fungerer det:**
- Hver side har nå en "Tilbake" knapp (grå) og "Fortsett" knapp (blå)
- "Fortsett til vaskeordning" er disabled til du har valgt minst 1 søppelbøtte
- Knappen får visuell feedback med opacity og cursor-not-allowed styling

**Tekniske detaljer:**
- CSS-klasser for disabled state (.btn-disabled)
- JavaScript validering i realtid
- Tilbake-funksjonalitet til alle steg

---

### ✅ 5. Rabatt-system basert på antall dunkevask

**Hva gjør det?**
- Rabatter beregnes automatisk basert på totalt antall dunkevask valgt
- Separate rabatt-ordninger for enkeltvask og sesongvask
- Alle rabatt-prosent er redigerbare i admin-panelet
- Prisvisning viser subtotal, rabatt og totalpris

**Enkeltvask rabatter:**
- 1 vask = 0% rabatt
- 2 vask = 50% rabatt
- 3-4 vask = 10% rabatt
- 5-9 vask = 20% rabatt
- 10-19 vask = 30% rabatt
- 20-29 vask = 40% rabatt
- 30-39 vask = 50% rabatt

**Sesongvask rabatter:**
- 10-19 dunkevask = 10% rabatt
- 20-39 dunkevask = 20% rabatt
- 40-59 dunkevask = 30% rabatt
- 60-89 dunkevask = 40% rabatt
- 90-999 dunkevask = 50% rabatt

**Slik redigerer du rabatter:**
1. Gå til **WordPress Admin → Settings → Juma Clean**
2. Finn **"Rabattordninger (basert på antall dunkevask)"** seksjonen
3. Rediger prosent-verdier for hver kategori
4. Klikk **"Save Changes"**

**Tekniske detaljer:**
- Rabatt-regler lagres som WordPress options
- Automatisk beregning basert på totalt antall bøtter
- Rabatter gjelder for hele ordren, ikke per bøtte
- Prisvisning inkluderer subtotal og rabatt-beløp

---

### ✅ 6. Postnummer-validering for tjenesteområde

**Hva gjør det?**
- Validerer at postnummer er innenfor Juma Clean sitt tjenesteområde
- Viser spesifikk feilmelding for områder utenfor dekning
- Forhindrer bestillinger fra ugyldige postnummer

**Gyldige postnummer:**
- **Tønsberg**: 3110-3118
- **Nøtterøy**: 3120-3128, 3140
- **Andre områder**: 3132 (Husøsund), 3133 (Duken), 3135 (Torød), 3138 (Skallestad), 3142 (Vestskogen), 3143 (Kjøpmannskjær), 3150-3154 (Tolvsrød), 3157 (Barkåker), 3159 (Melsomvik), 3160 (Stokke), 3170 (Sem), 3172-3173 (Vear), 3174 (Revetal), 3179 (Åsgårstrand)

**Feilmelding for ugyldige områder:**
> "Vi beklager, men vi tilbyr foreløpig ikke vasketjenester i ditt område. Ta gjerne kontakt med oss på info@jumaclean.no for mer informasjon, eller for å høre om vi kan gjøre et unntak."

**Tekniske detaljer:**
- JavaScript-validering med forhåndsdefinert liste over gyldige postnummer
- Umiddelbar tilbakemelding når bruker skriver inn ugyldig postnummer
- Forhindrer fremgang i skjemaet før gyldig postnummer er oppgitt

---

### ✅ 7. Forbedret mobilopplevelse og tekstendringer

**Hva er nytt:**
- ✅ **Enkel progress-indikator på mobil** - kun det aktive steget vises med større sirkel og tekst
- ✅ **Søppelbøtte-input starter på 0** - ikke lenger automatisk 1 når checkbox velges
- ✅ **Forbedret tekst** - "Antall liten/stor" endret til "Antall små/store søppelbøtter"

**Mobil progress-indikator:**
- På skjermer under 768px vises kun ett steg om gangen
- Større sirkel (3rem) og fet tekst for bedre lesbarhet
- Sentrerte elementer for bedre mobil-UX

**Tekst-endringer:**
- Alle søppelbøtte-labeler oppdatert til mer beskrivende tekst
- "Antall liten" → "Antall små søppelbøtter"
- "Antall stor" → "Antall store søppelbøtter"

**Tekniske detaljer:**
- CSS media queries for mobil-spesifikk styling
- JavaScript endret for å ikke sette default-verdi til 1
- HTML-labeler oppdatert på tvers av alle søppelbøtte-typer

---

### ✅ 8. Forbedret oppsummering med totalpris og dynamiske pakke-dager

**Hva er nytt:**
- ✅ **Totalpris i oppsummering** - Estimert pris vises nå i oppsummeringsboksen for både enkeltvask og sesongvask
- ✅ **Dynamiske pakke-dager** - Antall dager i parentes oppdateres automatisk når du endrer dem i admin-panelet
- ✅ **Rabatt-informasjon** - Oppsummeringen viser også rabatt-prosent hvis aktuelt

**Oppsummering viser nå:**
- Postnummer og kundetype
- Valgte søppelbøtter
- Vaskeordning med detaljer
- **Estimert totalpris med rabatt-info**

**Dynamiske pakke-dager:**
- "Liten vaskepakke (**19** vaskedager)" oppdateres automatisk
- "Medium vaskepakke (**22** vaskedager)" oppdateres automatisk
- "Stor vaskepakke (**26** vaskedager)" oppdateres automatisk

**Tekniske detaljer:**
- `showSummary()` funksjon utvidet med prisberegning
- `updatePackageDaysDisplay()` funksjon for dynamisk oppdatering
- Pakke-dager lastes fra server og oppdateres i UI ved sidelasting
- Pris-kalkulering i oppsummering bruker samme logikk som hovedprisberegning

---

## ✨ Neste steg

Pluginen er nå klar til bruk! Prøv å:
1. Test postnummer-validering: prøv et gyldig (f.eks. 3110) og et ugyldig postnummer
2. Test tilbake-knappene i skjemaet
3. Prøv å fortsette uten å velge bøtter - knappen skal være disabled
4. Test søppelbøtte-input: når du velger en type skal input starte på 0 (ikke 1)
5. Sjekk at teksten viser "Antall små/store søppelbøtter" i stedet for "liten/stor"
6. På mobil: se at kun ett steg vises om gangen med større sirkel
7. Gå til Settings → Juma Clean og endre noen rabatt-prosent
8. Sjekk at rabatter beregnes riktig når du velger mange bøtter
9. Test at oppsummeringen viser estimert totalpris
10. Endre pakke-dager i admin og se at de oppdateres dynamisk i frontend
11. Send en test-bestilling for å bekrefte nye priser og rabatter
12. Sjekk at den dukker opp i Google Sheets
13. Sjekk at alle felt vises i Juma Bestillinger

**God fornøyelse med de nye funksjonene!** 🎉





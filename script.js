// Juma Clean Form JavaScript - Ny struktur

// Gyldige postnummer for tjenesteområdet
const VALID_POSTNUMMER = [
  '3110', '3111', '3112', '3113', '3114', '3115', '3116', '3117', '3118', // Tønsberg
  '3120', '3121', '3122', '3123', '3124', '3125', '3126', '3127', '3128', // Nøtterøy
  '3132', // Husøsund
  '3133', // Duken
  '3135', // Torød
  '3138', // Skallestad
  '3140', // Nøtterøy
  '3142', // Vestskogen
  '3143', // Kjøpmannskjær
  '3150', '3151', '3152', '3153', '3154', // Tolvsrød
  '3157', // Barkåker
  '3159', // Melsomvik
  '3160', // Stokke
  '3170', // Sem
  '3172', '3173', // Vear
  '3174', // Revetal
  '3179'  // Åsgårstrand
];

// Globale variabler for priser og rabatter (lastes dynamisk)
let PRICES = {};
let PAKKE_DAGER = {};
let RABATTER = {};

let currentStep = 1;
let formData = {
  postnummer: '',
  customerType: '',
  bins: {},
  vaskeordning: '',
  vaskepakke: ''
};

// Steg-kontroll
function jumaSetProgress(step){
  for(let i=1;i<=5;i++){
    const h = document.getElementById('stepHead'+i);
    if(!h) continue;
    
    if(i < step){ 
      h.classList.add('completed'); 
      h.classList.remove('active'); 
      h.querySelector('.step-circle').textContent = '✓'; 
      h.removeAttribute('aria-current'); 
    }
    else if(i === step){ 
      h.classList.add('active'); 
      h.classList.remove('completed'); 
      h.querySelector('.step-circle').textContent = i; 
      h.setAttribute('aria-current','step'); 
    }
    else { 
      h.classList.remove('active','completed'); 
      h.querySelector('.step-circle').textContent = i; 
      h.removeAttribute('aria-current'); 
    }
  }
}

function jumaGoToStep(step){
  // Skjul alle steg
  for(let i=1;i<=6;i++){
    const el = document.getElementById('step'+i);
    if(el) el.classList.add('hidden');
  }
  
  // Vis valgt steg
  const targetStep = document.getElementById('step'+step);
  if(targetStep){
    targetStep.classList.remove('hidden');
    currentStep = step;
    jumaSetProgress(step);
  }
}

// STEG 1: Postnummer
function validatePostnummer(){
  const input = document.getElementById('postnummer');
  const value = input.value.trim();
  const err = document.querySelector('[data-error-for="postnummer"]');

  if(value.length === 4 && /^\d{4}$/.test(value)){
    // Sjekk om postnummeret er i tjenesteområdet
    if(VALID_POSTNUMMER.includes(value)){
      input.classList.remove('error');
      err.classList.add('hidden');
      formData.postnummer = value;

      // Automatisk videre til steg 2
      setTimeout(() => {
        jumaGoToStep(2);
      }, 300);

      return true;
    } else {
      // Ugyldig postnummer - vis spesifikk feilmelding
      input.classList.add('error');
      err.textContent = 'Vi beklager, men vi tilbyr foreløpig ikke vasketjenester i ditt område. Ta gjerne kontakt med oss på info@jumaclean.no for mer informasjon, eller for å høre om vi kan gjøre et unntak.';
      err.classList.remove('hidden');
      formData.postnummer = ''; // Clear invalid postnummer
      return false;
    }
  } else if(value.length > 0) {
    input.classList.add('error');
    err.textContent = 'Postnummer må være 4 siffer';
    err.classList.remove('hidden');
    formData.postnummer = ''; // Clear invalid postnummer
  } else {
    // Clear any existing error when field is empty
    err.classList.add('hidden');
    formData.postnummer = '';
  }
  return false;
}

// STEG 2: Kundetype
function handleCustomerTypeChange(){
  const select = document.getElementById('customerType');
  if(select.value){
    formData.customerType = select.value;
    jumaGoToStep(3);
  }
}

// STEG 3: Søppelbøtter - Checkbox toggle
function setupBinCheckboxes(){
  const binTypes = ['mat', 'rest', 'glass', 'papir', 'plast'];
  
  binTypes.forEach(type => {
    const checkbox = document.getElementById(`bin_${type}`);
    const countsDiv = document.getElementById(`bin_${type}_counts`);
    
    if(checkbox && countsDiv){
      checkbox.addEventListener('change', function(){
        if(this.checked){
          countsDiv.classList.remove('hidden');
          // La verdiene stå som de er (default 0)
        } else {
          countsDiv.classList.add('hidden');
          // Nullstill
          document.getElementById(`${type}_liten`).value = 0;
          document.getElementById(`${type}_stor`).value = 0;
        }
        updateBinsData();
        calculatePrice();
      });
    }
  });
  
  // Event listeners for antallsfelt
  const binInputs = document.querySelectorAll('.bin-counts input[type="number"]');
  binInputs.forEach(input => {
    input.addEventListener('input', () => {
      updateBinsData();
      calculatePrice();
    });
  });
}

function updateBinsData(){
  const binTypes = ['mat', 'rest', 'glass', 'papir', 'plast'];
  formData.bins = {};

  binTypes.forEach(type => {
    const liten = parseInt(document.getElementById(`${type}_liten`).value) || 0;
    const stor = parseInt(document.getElementById(`${type}_stor`).value) || 0;

    if(liten > 0 || stor > 0){
      formData.bins[type] = { liten, stor };
    }
  });

  // Oppdater knapp-status basert på om det er valgt bøtter
  updateContinueButton();
}

function updateContinueButton(){
  const continueBtn = document.getElementById('continueToStep4');
  const hasBins = Object.keys(formData.bins).length > 0;

  if(hasBins){
    continueBtn.disabled = false;
    continueBtn.classList.remove('btn-disabled');
  } else {
    continueBtn.disabled = true;
    continueBtn.classList.add('btn-disabled');
  }
}

// Oppdater pakke-dager display i UI
function updatePackageDaysDisplay(){
  const litenSpan = document.getElementById('pakke_liten_dager');
  const mediumSpan = document.getElementById('pakke_medium_dager');
  const storSpan = document.getElementById('pakke_stor_dager');

  if(litenSpan) litenSpan.textContent = PAKKE_DAGER.liten;
  if(mediumSpan) mediumSpan.textContent = PAKKE_DAGER.medium;
  if(storSpan) storSpan.textContent = PAKKE_DAGER.stor;
}

// Beregn rabatt basert på antall dunkevask
function calculateDiscount(totalBins, washType){
  if(!RABATTER[washType]) return 0;

  const discounts = RABATTER[washType];

  if(washType === 'enkeltvask'){
    if(totalBins === 1) return 0; // 1 vask = 0% rabatt
    if(totalBins === 2) return discounts['2'];
    if(totalBins >= 3 && totalBins <= 4) return discounts['3_4'];
    if(totalBins >= 5 && totalBins <= 9) return discounts['5_9'];
    if(totalBins >= 10 && totalBins <= 19) return discounts['10_19'];
    if(totalBins >= 20 && totalBins <= 29) return discounts['20_29'];
    if(totalBins >= 30 && totalBins <= 39) return discounts['30_39'];
  } else if(washType === 'sesongvask'){
    if(totalBins >= 10 && totalBins <= 19) return discounts['10_19'];
    if(totalBins >= 20 && totalBins <= 39) return discounts['20_39'];
    if(totalBins >= 40 && totalBins <= 59) return discounts['40_59'];
    if(totalBins >= 60 && totalBins <= 89) return discounts['60_89'];
    if(totalBins >= 90) return discounts['90_999'];
  }

  return 0;
}

function validateBins(){
  updateBinsData();
  const hasBins = Object.keys(formData.bins).length > 0;
  
  if(!hasBins){
    alert('Vennligst velg minst én søppelbøtte');
    return false;
  }
  return true;
}

function goToStep4(){
  if(validateBins()){
    jumaGoToStep(4);
    calculatePrice();
  }
}

// STEG 4: Vaskeordning
function setupVaskeordning(){
  const enkeltvask = document.getElementById('vaskeordning_enkel');
  const sesongvask = document.getElementById('vaskeordning_sesong');
  const pakkerDiv = document.getElementById('sesongvask_pakker');
  
  if(enkeltvask){
    enkeltvask.addEventListener('change', function(){
      if(this.checked){
        formData.vaskeordning = 'enkeltvask';
        formData.vaskepakke = '';
        pakkerDiv.classList.add('hidden');
        // Nullstill pakkevalg
        document.querySelectorAll('input[name="vaskepakke"]').forEach(el => el.checked = false);
        calculatePrice();
        showSummary();
      }
    });
  }
  
  if(sesongvask){
    sesongvask.addEventListener('change', function(){
      if(this.checked){
        formData.vaskeordning = 'sesongvask';
        pakkerDiv.classList.remove('hidden');
        // Nullstill pakkevalg når man velger sesongvask
        formData.vaskepakke = '';
        document.querySelectorAll('input[name="vaskepakke"]').forEach(el => el.checked = false);
        calculatePrice();
        showSummary();
      }
    });
  }
  
  // Pakkevalg for sesongvask
  document.querySelectorAll('input[name="vaskepakke"]').forEach(radio => {
    radio.addEventListener('change', function(){
      if(this.checked){
        formData.vaskepakke = this.value;
        calculatePrice();
        showSummary();
      }
    });
  });
}

// Prisberegning
function calculatePrice(){
  if(!formData.vaskeordning || Object.keys(formData.bins).length === 0){
    document.getElementById('priceLines').innerHTML = '<div class="muted">Velg søppelbøtter og vaskeordning for å se pris.</div>';
    document.getElementById('totalPrice').textContent = '0 kr';
    return;
  }

  let totalLiten = 0;
  let totalStor = 0;
  let totalBins = 0;

  // Tell opp totalt antall bøtter
  Object.values(formData.bins).forEach(bin => {
    totalLiten += bin.liten || 0;
    totalStor += bin.stor || 0;
    totalBins += (bin.liten || 0) + (bin.stor || 0);
  });

  let pris = 0;
  let rabatt = 0;
  let priceHTML = '';
  
  if(formData.vaskeordning === 'enkeltvask'){
    // Enkeltvask
    const prisLiten = totalLiten * PRICES.enkeltvask.liten;
    const prisStor = totalStor * PRICES.enkeltvask.stor;
    let subtotal = prisLiten + prisStor;

    // Beregn rabatt basert på totalt antall dunkevask
    rabatt = calculateDiscount(totalBins, 'enkeltvask');
    const rabattBelop = subtotal * (rabatt / 100);
    pris = subtotal - rabattBelop;
    
    if(totalLiten > 0){
      priceHTML += `<div class="price-line">
        <span>${totalLiten} stk små søppelbøtter</span>
        <strong>${prisLiten.toFixed(2).replace('.',',')} kr</strong>
      </div>`;
    }
    if(totalStor > 0){
      priceHTML += `<div class="price-line">
        <span>${totalStor} stk store søppelbøtter</span>
        <strong>${prisStor.toFixed(2).replace('.',',')} kr</strong>
      </div>`;
    }

    // Vis subtotal og rabatt
    priceHTML += `<div class="price-line" style="border-top: 1px solid var(--border); padding-top: 0.5rem; margin-top: 0.5rem;">
      <span>Subtotal</span>
      <strong>${subtotal.toFixed(2).replace('.',',')} kr</strong>
    </div>`;

    if(rabatt > 0){
      priceHTML += `<div class="price-line">
        <span>Rabatt (${rabatt}%)</span>
        <strong>-${rabattBelop.toFixed(2).replace('.',',')} kr</strong>
      </div>`;
    }
    
  } else if(formData.vaskeordning === 'sesongvask'){
    if(formData.vaskepakke){
      // Sesongvask med pakke
      const dager = PAKKE_DAGER[formData.vaskepakke];
      const prisPerDagLiten = PRICES.sesongvask.liten;
      const prisPerDagStor = PRICES.sesongvask.stor;

      const prisLiten = totalLiten * prisPerDagLiten * dager;
      const prisStor = totalStor * prisPerDagStor * dager;
      let subtotal = prisLiten + prisStor;

      // Beregn rabatt basert på totalt antall dunkevask
      rabatt = calculateDiscount(totalBins, 'sesongvask');
      const rabattBelop = subtotal * (rabatt / 100);
      pris = subtotal - rabattBelop;

      const pakkenavn = {
        liten: 'Liten vaskepakke',
        medium: 'Medium vaskepakke',
        stor: 'Stor vaskepakke'
      };

      priceHTML += `<div class="price-line">
        <span><strong>${pakkenavn[formData.vaskepakke]}</strong></span>
        <span><strong>${dager} vaskedager</strong></span>
      </div>`;

      if(totalLiten > 0){
        priceHTML += `<div class="price-line">
          <span>${totalLiten} stk små: ${totalLiten} × ${prisPerDagLiten} kr × ${dager} dager</span>
          <strong>${prisLiten.toFixed(2).replace('.',',')} kr</strong>
        </div>`;
      }
      if(totalStor > 0){
        priceHTML += `<div class="price-line">
          <span>${totalStor} stk store: ${totalStor} × ${prisPerDagStor} kr × ${dager} dager</span>
          <strong>${prisStor.toFixed(2).replace('.',',')} kr</strong>
        </div>`;
      }

      // Vis subtotal og rabatt for sesongvask
      priceHTML += `<div class="price-line" style="border-top: 1px solid var(--border); padding-top: 0.5rem; margin-top: 0.5rem;">
        <span>Subtotal</span>
        <strong>${subtotal.toFixed(2).replace('.',',')} kr</strong>
      </div>`;

      if(rabatt > 0){
        priceHTML += `<div class="price-line">
          <span>Rabatt (${rabatt}%)</span>
          <strong>-${rabattBelop.toFixed(2).replace('.',',')} kr</strong>
        </div>`;
      }
    } else {
      document.getElementById('priceLines').innerHTML = '<div class="muted">Velg vaskepakke for sesongvask.</div>';
      document.getElementById('totalPrice').textContent = '0 kr';
      return;
    }
  }
  
  // Beregn MVA hvis foretak
  if(formData.customerType === 'foretak'){
    const netto = pris;
    const mva = pris * 0.25;
    const total = pris + mva;
    
    priceHTML += `<div class="price-line" style="border-top: 1px solid var(--border); padding-top: 0.5rem; margin-top: 0.5rem;">
      <span>Netto pris</span>
      <strong>${netto.toFixed(2).replace('.',',')} kr</strong>
    </div>`;
    priceHTML += `<div class="price-line">
      <span>MVA 25%</span>
      <strong>${mva.toFixed(2).replace('.',',')} kr</strong>
    </div>`;
    
    document.getElementById('priceLines').innerHTML = priceHTML;
    document.getElementById('totalPrice').textContent = `${total.toFixed(2).replace('.',',')} kr`;
  } else {
    document.getElementById('priceLines').innerHTML = priceHTML;
    document.getElementById('totalPrice').textContent = `${pris.toFixed(2).replace('.',',')} kr`;
  }
}

// Oppsummering
function showSummary(){
  const summaryBox = document.getElementById('summaryBox');
  const summaryContent = document.getElementById('summaryContent');
  
  if(!formData.vaskeordning){
    summaryBox.classList.add('hidden');
    return;
  }
  
  let html = '<div style="font-size: 0.95rem; line-height: 1.8;">';
  
  // Postnummer
  html += `<p><strong>Postnummer:</strong> ${formData.postnummer}</p>`;
  
  // Kundetype
  const kundetypeNavn = formData.customerType === 'privatperson' ? 'Privatperson' : 'Foretak';
  html += `<p><strong>Kundetype:</strong> ${kundetypeNavn}</p>`;
  
  // Bøtter
  html += '<p><strong>Valgte søppelbøtter:</strong></p><ul style="margin: 0.5rem 0; padding-left: 1.5rem;">';
  Object.entries(formData.bins).forEach(([type, counts]) => {
    const typeNames = {
      mat: 'Matavfall',
      rest: 'Restavfall',
      glass: 'Glass- og metall',
      papir: 'Papp- og papir',
      plast: 'Plastemballasje'
    };
    if(counts.liten > 0) html += `<li>${typeNames[type]}: ${counts.liten} små</li>`;
    if(counts.stor > 0) html += `<li>${typeNames[type]}: ${counts.stor} store</li>`;
  });
  html += '</ul>';
  
  // Vaskeordning
  if(formData.vaskeordning === 'enkeltvask'){
    html += '<p><strong>Vaskeordning:</strong> Enkeltvask</p>';

    // Beregn og vis totalpris for enkeltvask
    if(Object.keys(formData.bins).length > 0){
      let totalLiten = 0;
      let totalStor = 0;
      let totalBins = 0;

      Object.values(formData.bins).forEach(bin => {
        totalLiten += bin.liten || 0;
        totalStor += bin.stor || 0;
        totalBins += (bin.liten || 0) + (bin.stor || 0);
      });

      const prisLiten = totalLiten * PRICES.enkeltvask.liten;
      const prisStor = totalStor * PRICES.enkeltvask.stor;
      let subtotal = prisLiten + prisStor;
      const rabatt = calculateDiscount(totalBins, 'enkeltvask');
      const rabattBelop = subtotal * (rabatt / 100);
      const totalPris = subtotal - rabattBelop;

      html += `<p><strong>Estimert totalpris:</strong> ${totalPris.toFixed(2).replace('.',',')} kr`;
      if(rabatt > 0){
        html += ` (inkl. ${rabatt}% rabatt)`;
      }
      html += '</p>';
    }
  } else if(formData.vaskeordning === 'sesongvask'){
    if(formData.vaskepakke){
      const pakkenavn = {
        liten: `Liten vaskepakke (${PAKKE_DAGER.liten} dager)`,
        medium: `Medium vaskepakke (${PAKKE_DAGER.medium} dager)`,
        stor: `Stor vaskepakke (${PAKKE_DAGER.stor} dager)`
      };
      html += `<p><strong>Vaskeordning:</strong> Sesongvask - ${pakkenavn[formData.vaskepakke]}</p>`;

      // Beregn og vis totalpris for sesongvask
      if(Object.keys(formData.bins).length > 0){
        let totalLiten = 0;
        let totalStor = 0;
        let totalBins = 0;

        Object.values(formData.bins).forEach(bin => {
          totalLiten += bin.liten || 0;
          totalStor += bin.stor || 0;
          totalBins += (bin.liten || 0) + (bin.stor || 0);
        });

        const dager = PAKKE_DAGER[formData.vaskepakke];
        const prisLiten = totalLiten * PRICES.sesongvask.liten * dager;
        const prisStor = totalStor * PRICES.sesongvask.stor * dager;
        let subtotal = prisLiten + prisStor;
        const rabatt = calculateDiscount(totalBins, 'sesongvask');
        const rabattBelop = subtotal * (rabatt / 100);
        const totalPris = subtotal - rabattBelop;

        html += `<p><strong>Estimert totalpris:</strong> ${totalPris.toFixed(2).replace('.',',')} kr`;
        if(rabatt > 0){
          html += ` (inkl. ${rabatt}% rabatt)`;
        }
        html += '</p>';
      }
    } else {
      html += '<p><strong>Vaskeordning:</strong> Sesongvask (velg pakke)</p>';
    }
  }

  html += '</div>';
  
  summaryContent.innerHTML = html;
  summaryBox.classList.remove('hidden');
}

// Form submission
function handleFormSubmit(e){
  e.preventDefault();
  
  // Valider kontaktdetaljer
  const requiredFields = ['fornavn','etternavn','gatenavn','husnummer','poststed','telefonnummer','epost'];
  let ok = true;
  
  requiredFields.forEach(id => {
    const el = document.getElementById(id);
    const msg = document.querySelector(`[data-error-for="${id}"]`);
    if(!el || !msg) return;
    
    if(!String(el.value||'').trim()){ 
      el.classList.add('error'); 
      msg.classList.remove('hidden'); 
      ok = false; 
    } else { 
      el.classList.remove('error'); 
      msg.classList.add('hidden'); 
    }
  });
  
  // Valider reCAPTCHA
  const recaptchaResponse = grecaptcha.getResponse();
  const recaptchaMsg = document.querySelector('[data-error-for="recaptcha"]');
  if(!recaptchaResponse){
    recaptchaMsg.classList.remove('hidden');
    ok = false;
  } else {
    recaptchaMsg.classList.add('hidden');
  }
  
  if(!ok) return;
  
  // Send til server
  const submitFormData = new FormData();
  submitFormData.append('action', 'juma_form_submit');
  submitFormData.append('nonce', juma_ajax.nonce);
  submitFormData.append('postnummer', formData.postnummer);
  submitFormData.append('customer_type', formData.customerType);
  submitFormData.append('vaskeordning', formData.vaskeordning);
  submitFormData.append('vaskepakke', formData.vaskepakke);
  
  // Bøtter
  Object.entries(formData.bins).forEach(([type, counts]) => {
    submitFormData.append(`${type}_liten`, counts.liten || 0);
    submitFormData.append(`${type}_stor`, counts.stor || 0);
  });
  
  // Kontaktinfo
  submitFormData.append('fornavn', document.getElementById('fornavn').value);
  submitFormData.append('etternavn', document.getElementById('etternavn').value);
  submitFormData.append('gatenavn', document.getElementById('gatenavn').value);
  submitFormData.append('husnummer', document.getElementById('husnummer').value);
  submitFormData.append('oppgang', document.getElementById('oppgang').value);
  submitFormData.append('poststed', document.getElementById('poststed').value);
  submitFormData.append('telefonnummer', document.getElementById('telefonnummer').value);
  submitFormData.append('epost', document.getElementById('epost').value);
  submitFormData.append('g-recaptcha-response', recaptchaResponse);
  
  fetch(juma_ajax.ajax_url, {
    method: 'POST',
    body: submitFormData
  })
  .then(response => response.json())
  .then(data => {
    if(data.success) {
      jumaGoToStep(6);
    } else {
      alert('Feil: ' + data.data);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('En feil oppstod. Prøv igjen.');
  });
}

// Initialize
document.addEventListener('DOMContentLoaded', async function() {
  // Last priser først
  await loadPrices();

  // Postnummer auto-advance
  const postnummerInput = document.getElementById('postnummer');
  if(postnummerInput){
    postnummerInput.addEventListener('input', validatePostnummer);
  }

  // Kundetype change
  const customerTypeSelect = document.getElementById('customerType');
  if(customerTypeSelect){
    customerTypeSelect.addEventListener('change', handleCustomerTypeChange);
  }

  // Setup bøtte checkboxes
  setupBinCheckboxes();

  // Setup vaskeordning
  setupVaskeordning();

  // Form submit
  const form = document.getElementById('juma-order-form');
  if(form){
    form.addEventListener('submit', handleFormSubmit);
  }

  // Start på steg 1 og init knapp-status
  jumaGoToStep(1);
  updateContinueButton();
});

// Last priser fra server
async function loadPrices(){
  try {
    const response = await fetch(juma_ajax.ajax_url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'action=juma_get_prices&nonce=' + juma_ajax.nonce
    });

    const data = await response.json();

    if(data.success){
      PRICES = {
        enkeltvask: data.data.enkeltvask,
        sesongvask: data.data.sesongvask
      };
      PAKKE_DAGER = data.data.pakker;
      RABATTER = data.data.rabatter;

      // Oppdater pakke-dager i UI
      updatePackageDaysDisplay();
    } else {
      // Fallback til default verdier hvis AJAX feiler
      console.warn('Kunne ikke laste priser fra server, bruker fallback verdier');
      PRICES = {
        enkeltvask: { liten: 229, stor: 279 },
        sesongvask: { liten: 179, stor: 229 }
      };
      PAKKE_DAGER = { liten: 19, medium: 22, stor: 26 };
      RABATTER = {
        enkeltvask: { '2': 50, '3_4': 10, '5_9': 20, '10_19': 30, '20_29': 40, '30_39': 50 },
        sesongvask: { '10_19': 10, '20_39': 20, '40_59': 30, '60_89': 40, '90_999': 50 }
      };

      // Oppdater pakke-dager i UI selv ved fallback
      updatePackageDaysDisplay();
    }
  } catch(error) {
    console.error('Feil ved lasting av priser:', error);
    // Fallback til default verdier
    PRICES = {
      enkeltvask: { liten: 229, stor: 279 },
      sesongvask: { liten: 179, stor: 229 }
    };
    PAKKE_DAGER = { liten: 19, medium: 22, stor: 26 };
    RABATTER = {
      enkeltvask: { '2': 50, '3_4': 10, '5_9': 20, '10_19': 30, '20_29': 40, '30_39': 50 },
      sesongvask: { '10_19': 10, '20_39': 20, '40_59': 30, '60_89': 40, '90_999': 50 }
    };

    // Oppdater pakke-dager i UI selv ved error
    updatePackageDaysDisplay();
  }
}

// Global function for å gå til step 5 (kontakt)
function goToStep5(){
  if(!formData.vaskeordning){
    alert('Vennligst velg vaskeordning');
    return;
  }
  if(formData.vaskeordning === 'sesongvask' && !formData.vaskepakke){
    alert('Vennligst velg vaskepakke');
    return;
  }
  jumaGoToStep(5);
}

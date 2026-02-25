/* Vehicles search and page prefill
   - On index: search by plate in vehicles_mock_500.json and display result + link to prestations
   - On prestations: read query params and populate the car fields
*/
(function () {
  const DATA_URL = 'vehicles_mock_500.json';
  let vehiclesCache = null;

  function normalizePlate(s = '') {
    return s.replace(/[^A-Z0-9]/gi, '').toUpperCase();
  }

  async function loadVehicles() {
    if (vehiclesCache) return vehiclesCache;
    try {
      const res = await fetch(DATA_URL);
      if (!res.ok) throw new Error('failed to fetch vehicles');
      vehiclesCache = await res.json();
      return vehiclesCache;
    } catch (e) {
      console.warn('Could not load vehicles data', e);
      vehiclesCache = [];
      return vehiclesCache;
    }
  }

  // Index page behavior
  function initIndex() {
    const input = document.getElementById('plate-input');
    const btn = document.getElementById('plate-search-btn');
    const result = document.getElementById('plate-result');
    if (!input || !btn || !result) return;

    const brandInput = document.querySelector('input[placeholder="Marque.."]');
    const modelInput = document.querySelector('input[placeholder="Modèle.."]');
    const yearInput = document.querySelector('input[placeholder="Année.."]');
    const versionInput = document.querySelector('input[placeholder="Version.."]');

    btn.addEventListener('click', async () => {
      const q = input.value.trim();
      result.textContent = '';
      if (!q) {
        result.innerHTML = '<small class="text-danger">Entrez une immatriculation.</small>';
        return;
      }
      result.innerHTML = '<small>Recherche…</small>';
      const vehicles = await loadVehicles();
      const norm = normalizePlate(q);
      const found = vehicles.find(v => normalizePlate(v.plate) === norm);
      if (!found) {
        result.innerHTML = '<small class="text-danger">Aucun véhicule trouvé.</small>';
        if (brandInput) brandInput.value = '';
        if (modelInput) modelInput.value = '';
        if (yearInput) yearInput.value = '';
        if (versionInput) versionInput.value = '';
        return;
      }

      // populate inputs if present
      if (brandInput) brandInput.value = found.brand || '';
      if (modelInput) modelInput.value = found.model || '';
      if (yearInput) yearInput.value = found.year || '';
      if (versionInput) versionInput.value = found.vin || '';

      // Redirect immediately to prestations with important params
      const params = new URLSearchParams({
        plate: found.plate,
        brand: found.brand,
        model: found.model,
        year: found.year,
        fuel: found.fuel,
        power: found.power_hp
      });

      location.href = `prestations.html?${params.toString()}`;
    });
    // support Enter key on input
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        btn.click();
      }
    });
  }

  // Prestations page behavior: read query params and fill the spans/fields
  function initPrestations() {
    if (!location.search) return;
    const params = new URLSearchParams(location.search);
    const plate = params.get('plate');
    const brand = params.get('brand');
    const model = params.get('model');
    const year = params.get('year');
    const fuel = params.get('fuel');
    const power = params.get('power');

    // Fill the spans we have: [0]=name, [1]=power, [2]=fuel
    const spans = document.querySelectorAll('.car-value');
    if (spans && spans.length >= 3) {
      if (brand) spans[0].textContent = `${brand} ${model || ''}`.trim();
      else if (plate) spans[0].textContent = plate;
      if (power) spans[1].textContent = `${power} ch`;
      if (fuel) spans[2].textContent = fuel;
    }

    // add a small summary alert at the top
    const main = document.querySelector('main');
    if (main && (brand || plate)) {
      const node = document.createElement('div');
      node.className = 'alert alert-info mt-3';
      node.innerHTML = `<strong>Véhicule :</strong> ${brand || '—'} ${model || ''} ${power ? '— ' + power + ' ch' : ''} ${plate ? '— ' + plate : ''}`;
      main.prepend(node);
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    initIndex();
    initPrestations();
  });

})();

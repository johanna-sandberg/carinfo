const form = document.getElementById('f');
const results = document.getElementById('results');
const nextButton = document.getElementById('next');
const prevButton = document.getElementById('prev');
const pager = document.getElementById('pager');
let offset = 0, limit = 25, lastCount = 0;

async function fetchData() {
  const fd = new FormData(form);
  const params = new URLSearchParams();
  for (const [k,v] of fd.entries()) if (v) params.set(k, v);
  params.set('limit', String(limit));
  params.set('offset', String(offset));

  const res = await fetch(`/api/search.php?` + params.toString());
  const data = await res.json();
  console.log('Response from backend:', data);

  const items = Array.isArray(data.items) ? data.items : [];
  lastCount = items.length;

  if (data.received) {
    results.innerHTML = '';
    const pre = document.createElement('pre');
    pre.className = 'p-3 bg-white border rounded';
    pre.textContent = JSON.stringify(data.received, null, 2);
    results.appendChild(pre);
  } else {
    render(items);
  }

  updatePager();
}

function render(items) {
  results.innerHTML = '';
  for (const it of items) {
    const div = document.createElement('div');
    div.className = 'col';
    div.innerHTML = `
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <h5 class="card-title m-0">${escapeHtml(it.title || `${it.brand||''} ${it.model||''}`)}</h5>
            <span class="badge text-bg-secondary">${it.model_year||''}</span>
          </div>
          <p class="card-text m-0">${escapeHtml([it.reg_plate, it.fuel, it.gearbox, it.body].filter(Boolean).join(' • '))}</p>
          <p class="card-text m-0">${it.mileage_km? Intl.NumberFormat('sv-SE').format(it.mileage_km)+' km' : ''}</p>
          <p class="card-text fw-bold m-0">${it.price_sek? Intl.NumberFormat('sv-SE').format(it.price_sek)+' kr' : 'Pris saknas'}</p>
          <a class="small" href="${it.source_url}" target="_blank" rel="noopener">Visa annons</a>
        </div>
      </div>`;
    results.appendChild(div);
  }
}

function escapeHtml(s){return s.replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]))}

function updatePager() {
  const hasResults = lastCount > 0;
  const onSecondPageOrMore = offset >= limit;
  const isLastPage = lastCount < limit;
  const showPrev = hasResults && onSecondPageOrMore;
  const showNext = hasResults && !isLastPage;
  const showPager = showPrev || showNext;

  pager.style.display = showPager ? 'flex' : 'none';
  prevButton.style.display = showPrev ? '' : 'none';
  nextButton.style.display = showNext ? '' : 'none';
}

form.addEventListener('submit', e => { e.preventDefault(); offset=0; fetchData(); });
nextButton.addEventListener('click', ()=>{ offset += limit; fetchData(); });
prevButton.addEventListener('click', ()=>{ offset = Math.max(0, offset - limit); fetchData(); });

fetchData();

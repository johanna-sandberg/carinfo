const form = document.getElementById('f')
const results = document.getElementById('results')
const nextButton = document.getElementById('next')
const prevButton = document.getElementById('prev')
const pager = document.getElementById('pager')
const meta = document.getElementById('meta')

let offset = 0, limit = 25, lastCount = 0

async function fetchData() {
  const formData = new FormData(form)
  const params = new URLSearchParams()
  for (const [k, v] of formData.entries()) if (v) params.set(k, v)
  params.set('limit', String(limit))
  params.set('offset', String(offset))

  const res = await fetch(`/api/search.php?` + params.toString(), { headers: { 'Accept': 'application/json' } })
  const data = await res.json()

  const items = Array.isArray(data.items) ? data.items : []
  lastCount = items.length

  render(items)
  updatePager(data.offset ?? offset, data.limit ?? limit, data.count ?? lastCount)
}

function render(items) {
  results.innerHTML = ''
  if (items.length === 0) {
    const empty = document.createElement('div')
    empty.className = 'col'
    empty.innerHTML = `<div class="alert alert-light border">Inga träffar</div>`
    results.appendChild(empty)
    return
  }

  for (const it of items) {
    const title = it.title || [it.brand, it.model].filter(Boolean).join(' ')
    const subtitle = [it.reg_plate, it.fuel, it.gearbox, it.body].filter(Boolean).join(' • ')
    const km = it.mileage_km ? Intl.NumberFormat('sv-SE').format(it.mileage_km) + ' km' : ''
    const price = it.price_sek ? Intl.NumberFormat('sv-SE').format(it.price_sek) + ' kr' : 'Pris saknas'
    const yearBadge = it.model_year ? `<span class="badge text-bg-secondary">${it.model_year}</span>` : ''

    const div = document.createElement('div')
    div.className = 'col'
    div.innerHTML = `
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <h5 class="card-title m-0">${escapeHtml(title)}</h5>
            ${yearBadge}
          </div>
          <p class="card-text m-0">${escapeHtml(subtitle)}</p>
          <p class="card-text m-0">${km}</p>
          <p class="card-text fw-bold m-0">${price}</p>
          ${it.source_url ? `<a class="small" href="${it.source_url}" target="_blank" rel="noopener">Visa annons</a>` : ''}
        </div>
      </div>`
    results.appendChild(div)
  }
}

function escapeHtml(s) { return String(s).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m])) }

function updatePager(currentOffset, currentLimit, shownCount) {
  const onSecondPageOrMore = currentOffset >= currentLimit
  const isLastPage = shownCount < currentLimit
  const showPrev = shownCount > 0 && onSecondPageOrMore
  const showNext = shownCount > 0 && !isLastPage
  const showPager = showPrev || showNext

  pager.style.display = showPager ? 'flex' : 'none'
  prevButton.style.display = showPrev ? '' : 'none'
  nextButton.style.display = showNext ? '' : 'none'

  const page = Math.floor(currentOffset / currentLimit) + 1
  meta.textContent = `Visar ${Math.min(currentLimit, shownCount)} resultat • Sida ${page}`
}

form.addEventListener('submit', e => { e.preventDefault(); offset = 0; fetchData() })
nextButton.addEventListener('click', () => { offset += limit; fetchData() })
prevButton.addEventListener('click', () => { offset = Math.max(0, offset - limit); fetchData() })

fetchData()

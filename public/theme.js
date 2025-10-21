const root = document.documentElement
const toggle = document.getElementById('themeToggle')
const label = document.getElementById('themeLabel')
const key = 'theme'

function detectedTheme() {
  return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

function storedTheme() {
  const s = localStorage.getItem(key)
  return s === 'light' || s === 'dark' ? s : null
}

function applyTheme(theme) {
  root.setAttribute('data-bs-theme', theme)
  toggle.checked = theme === 'light'
  label.textContent = theme === 'light' ? 'Ljust' : 'Mörkt'
}

function setUserTheme(theme) {
  localStorage.setItem(key, theme)
  applyTheme(theme)
}

function initTheme() {
  const chosen = storedTheme() || detectedTheme()
  applyTheme(chosen)
}

initTheme()

let mediaListenerActive = !storedTheme()
const media = window.matchMedia('(prefers-color-scheme: dark)')
function onMediaChange() {
  if (!mediaListenerActive) return
  applyTheme(media.matches ? 'dark' : 'light')
}
media.addEventListener('change', onMediaChange)

toggle.addEventListener('change', () => {
  mediaListenerActive = false
  setUserTheme(toggle.checked ? 'light' : 'dark')
})

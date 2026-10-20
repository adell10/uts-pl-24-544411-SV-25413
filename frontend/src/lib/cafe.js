// Konstanta & helper tampilan kafe (sama dengan nilai di backend)
export const COLORS = {
  pink: '#F4B7A8',
  yellow: '#F2C14E',
  purple: '#C9B8E8',
  green: '#A9BE8A',
  blue: '#9EC1EE',
}

export const COLOR_LABELS = {
  pink: 'Pink',
  yellow: 'Kuning',
  purple: 'Ungu',
  green: 'Hijau',
  blue: 'Biru',
}

export const FACILITIES = {
  wifi: 'Wifi',
  colokan: 'Colokan',
  musala: 'Musala',
  parkir: 'Parkir mobil',
}

export const STATUS_LABELS = {
  visited: 'Sudah mampir',
  wishlist: 'Mau dicoba',
}

export const priceText = (level) => Array.from({ length: level || 1 }, () => 'Rp').join(' ')

export function formatDate(value) {
  if (!value) return '-'
  return new Date(`${value}T00:00:00`).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

export function todayISO() {
  const d = new Date()
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}
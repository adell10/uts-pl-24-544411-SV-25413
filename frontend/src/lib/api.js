import axios from 'axios'
import { auth } from './auth'

// Alamat API TIDAK ditulis mati: diambil dari variabel lingkungan Vite.
// Lokal: VITE_API_URL=http://localhost:8000/api  |  Docker: VITE_API_URL=/api
export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: { Accept: 'application/json' },
})

// Kirim token di SETIAP permintaan
api.interceptors.request.use((config) => {
  if (auth.token) {
    config.headers.Authorization = `Bearer ${auth.token}`
  }
  return config
})

// Kalau server membalas 401 (token habis/tidak valid) → keluar otomatis
let onUnauthorized = () => {}
export function setUnauthorizedHandler(fn) {
  onUnauthorized = fn
}

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 && auth.token) {
      auth.clear()
      onUnauthorized()
    }
    return Promise.reject(error)
  },
)

/** Ambil pesan error per field dari respons 422 Laravel → { name: 'Nama kafe wajib diisi.' } */
export function fieldErrors(error) {
  const errors = error.response?.data?.errors ?? {}
  return Object.fromEntries(Object.entries(errors).map(([key, msgs]) => [key, msgs[0]]))
}

/** Pesan umum yang ramah untuk ditampilkan di toast */
export function errorMessage(error, fallback = 'Terjadi kesalahan. Coba lagi.') {
  if (!error.response) return 'Gagal terhubung ke server.'
  return error.response.data?.message ?? fallback
}
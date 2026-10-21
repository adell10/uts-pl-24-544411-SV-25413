import { reactive } from 'vue'

let nextId = 1

export const toasts = reactive([])

function push(type, message) {
  const id = nextId++
  toasts.push({ id, type, message })
  setTimeout(() => dismiss(id), 3500)
}

export function dismiss(id) {
  const i = toasts.findIndex((t) => t.id === id)
  if (i !== -1) toasts.splice(i, 1)
}

export const toast = {
  success: (message) => push('success', message),
  error: (message) => push('error', message),
}
import { reactive } from 'vue'

// Token & data user disimpan di localStorage supaya tetap login setelah refresh.
const read = (key) => {
  try { return localStorage.getItem(key) } catch { return null }
}
const write = (key, value) => {
  try {
    if (value === null) localStorage.removeItem(key)
    else localStorage.setItem(key, value)
  } catch { /* abaikan (mis. mode private) */ }
}

const savedUser = read('user')

export const auth = reactive({
  token: read('token'),
  user: savedUser ? JSON.parse(savedUser) : null,

  get isLoggedIn() {
    return Boolean(this.token)
  },

  setSession(token, user) {
    this.token = token
    this.user = user
    write('token', token)
    write('user', JSON.stringify(user))
  },

  clear() {
    this.token = null
    this.user = null
    write('token', null)
    write('user', null)
  },
})
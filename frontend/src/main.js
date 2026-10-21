import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { setUnauthorizedHandler } from './lib/api'
import { toast } from './lib/toast'
import './style.css'

setUnauthorizedHandler(() => {
  toast.error('Sesi habis, silakan masuk lagi.')
  router.push({ name: 'login' })
})

createApp(App).use(router).mount('#app')
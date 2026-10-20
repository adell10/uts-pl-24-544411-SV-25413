<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import CupIcon from './CupIcon.vue'
import { api } from '../lib/api'
import { auth } from '../lib/auth'
import { toast } from '../lib/toast'

defineProps({ showAdd: { type: Boolean, default: true } })
const emit = defineEmits(['add'])

const router = useRouter()
const leaving = ref(false)

async function logout() {
  leaving.value = true
  try {
    await api.post('/logout')
  } catch {
    // token mungkin sudah tidak berlaku — tetap keluar
  } finally {
    auth.clear()
    leaving.value = false
    toast.success('Sampai jumpa lagi!')
    router.push({ name: 'login' })
  }
}
</script>

<template>
  <header class="app-header">
    <RouterLink to="/" class="logo hand">
      <span class="logo-tile"><CupIcon :size="24" /></span>
      cafe hopper<span class="dot">.</span>
    </RouterLink>

    <div class="right">
      <button v-if="showAdd" class="btn btn-primary" @click="emit('add')">
        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14" /></svg>
        Tambah kafe
      </button>
      <div class="user-pill">
        <span class="avatar hand" aria-hidden="true">{{ auth.user?.name?.charAt(0) ?? '?' }}</span>
        <span class="name">{{ auth.user?.name }}</span>
        <button class="logout" :disabled="leaving" @click="logout">{{ leaving ? '…' : 'Keluar' }}</button>
      </div>
    </div>
  </header>
</template>

<style scoped>
.app-header { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; }
.logo { display: flex; align-items: center; gap: 10px; font-size: 34px; line-height: 1; color: var(--ink); text-decoration: none; }
.logo:hover { color: var(--ink); }
.logo-tile {
  background: var(--yellow); border: var(--border); border-radius: 12px; width: 44px; height: 44px;
  display: flex; align-items: center; justify-content: center; transform: rotate(-6deg); box-shadow: 3px 3px 0 var(--ink);
}
.dot { color: var(--navy); }
.right { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.user-pill {
  display: flex; align-items: center; gap: 8px; background: var(--paper);
  border: var(--border); border-radius: 99px; padding: 4px 6px 4px 4px;
}
.avatar {
  width: 38px; height: 38px; border-radius: 50%; background: var(--pink); border: 2px solid var(--ink);
  display: flex; align-items: center; justify-content: center; font-size: 19px;
}
.name { font-weight: 800; font-size: 14px; }
.logout {
  height: 36px; padding: 0 12px; border: 0; border-radius: 99px; background: var(--ink); color: var(--paper);
  font: inherit; font-weight: 800; font-size: 13px; cursor: pointer;
}
</style>
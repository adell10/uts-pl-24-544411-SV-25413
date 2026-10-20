<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue'

defineProps({
  title: { type: String, required: true },
  message: { type: String, default: '' },
  confirmText: { type: String, default: 'Ya, hapus' },
  busy: { type: Boolean, default: false },
})
const emit = defineEmits(['confirm', 'cancel'])

const cancelBtn = ref(null)
const onKey = (e) => { if (e.key === 'Escape') emit('cancel') }
onMounted(() => { cancelBtn.value?.focus(); window.addEventListener('keydown', onKey) })
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))
</script>

<template>
  <div class="overlay" @click.self="emit('cancel')">
    <div class="card confirm" role="alertdialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-msg">
      <div class="icon" aria-hidden="true">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#B23A1E" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6" /></svg>
      </div>
      <h2 id="confirm-title" class="hand">{{ title }}</h2>
      <p id="confirm-msg">{{ message }}</p>
      <div class="actions">
        <button ref="cancelBtn" class="btn" :disabled="busy" @click="emit('cancel')">Batal</button>
        <button class="btn btn-danger" :disabled="busy" @click="emit('confirm')">{{ busy ? 'Menghapus…' : confirmText }}</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.overlay { align-items: center; }
.confirm { width: 100%; max-width: 500px; padding: 32px; display: flex; flex-direction: column; align-items: center; gap: 14px; text-align: center; }
.icon {
  width: 72px; height: 72px; border-radius: 50%; background: var(--pink); border: var(--border);
  display: flex; align-items: center; justify-content: center; transform: rotate(-6deg);
}
h2 { font-size: 34px; line-height: 1.05; }
p { margin: 0; font-size: 15px; color: #4a4652; line-height: 1.5; }
.actions { display: flex; gap: 10px; margin-top: 8px; width: 100%; }
.actions .btn { flex: 1; }
</style>
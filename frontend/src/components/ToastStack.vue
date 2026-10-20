<script setup>
import { toasts, dismiss } from '../lib/toast'
</script>

<template>
  <div class="toast-stack">
    <TransitionGroup name="toast">
      <div
        v-for="t in toasts"
        :key="t.id"
        class="toast"
        :role="t.type === 'error' ? 'alert' : 'status'"
      >
        <span class="toast-icon" :class="t.type" aria-hidden="true">
          <svg v-if="t.type === 'success'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#23212B" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7" /></svg>
          <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#B23A1E" stroke-width="3" stroke-linecap="round"><path d="M12 7v6M12 17h.01" /></svg>
        </span>
        <span class="toast-msg">{{ t.message }}</span>
        <button class="toast-close" aria-label="Tutup notifikasi" @click="dismiss(t.id)">×</button>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-stack {
  position: fixed; right: 24px; bottom: 24px; z-index: 100;
  display: flex; flex-direction: column; gap: 12px; width: min(360px, calc(100vw - 32px));
}
.toast {
  display: flex; align-items: center; gap: 12px; background: var(--paper);
  border: var(--border); border-radius: 14px; box-shadow: var(--shadow); padding: 12px 14px;
}
.toast-icon {
  width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px; border: 2px solid var(--ink);
  display: flex; align-items: center; justify-content: center;
}
.toast-icon.success { background: var(--green); }
.toast-icon.error { background: var(--pink); }
.toast-msg { flex: 1; font-weight: 800; font-size: 14px; }
.toast-close { width: 32px; height: 32px; border: 0; background: transparent; font-size: 20px; font-weight: 800; cursor: pointer; }
.toast-enter-active, .toast-leave-active { transition: all .2s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(12px); }
</style>
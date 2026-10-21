<script setup>
defineProps({
  value: { type: Number, default: null },
  size: { type: Number, default: 15 },
})
</script>

<template>
  <span v-if="value" class="stars" :style="{ fontSize: size + 'px' }" :aria-label="`Rating ${value} dari 5`">
    <span v-for="i in 5" :key="i" :class="{ off: i > value }" aria-hidden="true">★</span>
  </span>
  <span v-else class="no-rating">Belum ada rating</span>
</template>

<style scoped>
.no-rating { font-size: 13px; font-weight: 700; color: var(--muted); }
</style>
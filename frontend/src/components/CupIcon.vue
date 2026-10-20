<script setup>
defineProps({
  size: { type: Number, default: 24 },
  fill: { type: String, default: 'none' },
  stroke: { type: String, default: 'currentColor' },
  strokeWidth: { type: Number, default: 2.4 },
})
</script>

<template>
  <svg
    aria-hidden="true"
    :width="size"
    :height="size"
    viewBox="0 0 24 24"
    :fill="fill"
    :stroke="stroke"
    :stroke-width="strokeWidth"
    stroke-linecap="round"
    stroke-linejoin="round"
  >
    <path d="M17 8h1a4 4 0 0 1 0 8h-1" />
    <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4z" />
    <path d="M6 2v2M10 2v2M14 2v2" fill="none" />
  </svg>
</template>
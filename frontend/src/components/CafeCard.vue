<script setup>
import { computed } from 'vue'
import StarRating from './StarRating.vue'
import { COLORS, priceText } from '../lib/cafe'

const props = defineProps({ cafe: { type: Object, required: true } })
const wishlist = computed(() => props.cafe.status === 'wishlist')
</script>

<template>
  <RouterLink :to="{ name: 'detail', params: { id: cafe.id } }" class="cafe-card" :class="{ wishlist }">
    <div class="top" :style="{ background: wishlist ? '#E4ECF8' : COLORS[cafe.color] }">
      <span class="hand title">{{ cafe.name }}</span>
      <span class="price">{{ priceText(cafe.price_level) }}</span>
    </div>
    <div class="body">
      <span class="area">{{ cafe.area }}</span>
      <StarRating :value="cafe.rating" />
      <div class="badges">
        <span v-if="cafe.work_friendly" class="badge" style="background: var(--green)">Spot nugas</span>
        <span v-if="wishlist" class="badge" style="background: var(--yellow)">Mau dicoba</span>
        <span v-else class="badge" style="background: var(--bg)">Sudah mampir</span>
      </div>
    </div>
    <span v-if="wishlist" class="next hand" aria-hidden="true">next!</span>
  </RouterLink>
</template>

<style scoped>
.cafe-card {
  position: relative; text-decoration: none; color: var(--ink); background: var(--paper);
  border: var(--border); border-radius: 18px; box-shadow: 5px 5px 0 var(--ink); overflow: hidden;
  display: flex; flex-direction: column; transition: transform .15s ease, box-shadow .15s ease;
}
.cafe-card:hover { color: var(--ink); transform: translate(-2px, -2px) rotate(-.6deg); box-shadow: 7px 7px 0 var(--ink); }
.cafe-card.wishlist { border-style: dashed; background: rgba(255, 253, 248, .7); box-shadow: none; }
.top {
  min-height: 82px; border-bottom: var(--border); padding: 12px 14px;
  display: flex; align-items: flex-end; justify-content: space-between; gap: 8px;
}
.wishlist .top { border-bottom-style: dashed; }
.title { font-size: 25px; line-height: 1; }
.price {
  font-size: 12px; font-weight: 800; background: var(--paper); border: 2px solid var(--ink);
  border-radius: 99px; padding: 2px 8px; white-space: nowrap;
}
.body { padding: 12px 14px 16px; display: flex; flex-direction: column; gap: 8px; }
.area { font-size: 13px; font-weight: 700; color: #3d3a45; }
.badges { display: flex; gap: 6px; flex-wrap: wrap; }
.badges .badge { font-size: 11px; padding: 3px 8px; border-width: 1.5px; }
.next {
  position: absolute; right: 12px; bottom: 14px; border: 2.5px solid var(--navy); color: var(--navy);
  border-radius: 8px; padding: 0 8px; font-size: 17px; transform: rotate(-8deg);
}
</style>
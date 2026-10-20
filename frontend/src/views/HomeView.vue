<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import CafeCard from '../components/CafeCard.vue'
import CafeFormModal from '../components/CafeFormModal.vue'
import CupIcon from '../components/CupIcon.vue'
import StarRating from '../components/StarRating.vue'
import { api, errorMessage } from '../lib/api'
import { toast } from '../lib/toast'
import { COLORS, priceText } from '../lib/cafe'

const router = useRouter()
const cafes = ref([])
const loading = ref(true)
const loadError = ref('')
const showForm = ref(false)

const filter = ref('all')            // all | visited | wishlist
const onlyNugas = ref(false)
const search = ref('')

async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await api.get('/cafes')   // GET /api/cafes (pakai token)
    cafes.value = data.data
  } catch (error) {
    if (error.response?.status !== 401) loadError.value = errorMessage(error)
  } finally {
    loading.value = false
  }
}
onMounted(load)

const stats = computed(() => ({
  total: cafes.value.length,
  visited: cafes.value.filter((c) => c.status === 'visited').length,
  wishlist: cafes.value.filter((c) => c.status === 'wishlist').length,
  nugas: cafes.value.filter((c) => c.work_friendly).length,
}))

// Kafe favorit = rating tertinggi, lalu paling sering didatangi
const favorite = computed(() =>
  [...cafes.value]
    .filter((c) => c.status === 'visited')
    .sort((a, b) => (b.rating ?? 0) - (a.rating ?? 0) || b.visit_count - a.visit_count)[0] ?? null,
)

const shown = computed(() => {
  const q = search.value.trim().toLowerCase()
  return cafes.value.filter((c) =>
    (filter.value === 'all' || c.status === filter.value) &&
    (!onlyNugas.value || c.work_friendly) &&
    (!q || c.name.toLowerCase().includes(q) || c.area.toLowerCase().includes(q)),
  )
})

function onSaved(cafe) {
  showForm.value = false
  cafes.value.unshift(cafe)
}

const tabs = computed(() => [
  { key: 'all', label: `Semua · ${stats.value.total}` },
  { key: 'visited', label: `Sudah mampir · ${stats.value.visited}` },
  { key: 'wishlist', label: `Mau dicoba · ${stats.value.wishlist}` },
])
</script>

<template>
  <div class="page">
    <AppHeader @add="showForm = true" />

    <div v-if="loading" class="spinner" role="status"><span class="sr-only">Memuat…</span></div>

    <div v-else-if="loadError" class="card state">
      <h2 class="hand">Ups, gagal memuat.</h2>
      <p>{{ loadError }}</p>
      <button class="btn btn-primary" @click="load">Coba lagi</button>
    </div>

    <!-- Kosong: pengguna baru -->
    <section v-else-if="!cafes.length" class="empty">
      <div class="card state">
        <span class="tape" style="left: 50%; margin-left: -60px; transform: rotate(-3deg)" aria-hidden="true"></span>
        <div class="empty-icon" aria-hidden="true"><CupIcon :size="52" fill="#FFFDF8" :stroke-width="1.8" /></div>
        <h1 class="hand">Belum ada kafe<br />yang dicatat.</h1>
        <p>Mulai dari kafe terakhir yang kamu datangi.</p>
        <button class="btn btn-primary" style="height: 52px; font-size: 22px" @click="showForm = true">+ Tambah kafe pertama</button>
      </div>
    </section>

    <template v-else>
      <section class="top-row">
        <div v-if="favorite" class="card fav" role="link" tabindex="0"
          @click="router.push({ name: 'detail', params: { id: favorite.id } })"
          @keydown.enter="router.push({ name: 'detail', params: { id: favorite.id } })">
          <span class="tape" style="left: 40px" aria-hidden="true"></span>
          <div class="fav-img" :style="{ background: COLORS[favorite.color] }" aria-hidden="true">
            <CupIcon :size="84" fill="#FFFDF8" :stroke-width="1.6" />
          </div>
          <div class="fav-info">
            <span class="label">Kafe favoritmu</span>
            <h1 class="hand">{{ favorite.name }}</h1>
            <span class="meta">{{ favorite.area }} · {{ priceText(favorite.price_level) }}</span>
            <div class="row">
              <StarRating :value="favorite.rating" :size="18" />
              <span v-if="favorite.work_friendly" class="badge" style="background: var(--green)">Spot nugas</span>
            </div>
            <span v-if="favorite.favorite_menu" class="hand menu">Menu andalan: {{ favorite.favorite_menu }}</span>
          </div>
          <span v-if="favorite.visit_count > 1" class="stamp" style="right: 24px; top: 20px">{{ favorite.visit_count }}x mampir!</span>
        </div>

        <div class="stickies">
          <div class="sticky" style="background: var(--yellow); transform: rotate(-2.5deg)"><span class="num">{{ stats.total }}</span><span class="txt">total kafe</span></div>
          <div class="sticky" style="background: var(--green); transform: rotate(2deg)"><span class="num">{{ stats.visited }}</span><span class="txt">sudah mampir</span></div>
          <div class="sticky" style="background: var(--purple); transform: rotate(1.5deg)"><span class="num">{{ stats.wishlist }}</span><span class="txt">mau dicoba</span></div>
          <div class="sticky" style="background: var(--pink); transform: rotate(-2deg)"><span class="num">{{ stats.nugas }}</span><span class="txt">spot nugas</span></div>
        </div>
      </section>

      <div class="toolbar">
        <h2 class="hand">Daftar kafe</h2>
        <div role="tablist" aria-label="Status kunjungan" class="tabs">
          <button v-for="t in tabs" :key="t.key" role="tab" class="chip" :aria-selected="filter === t.key" @click="filter = t.key">{{ t.label }}</button>
        </div>
        <label class="chip chip-dashed"><input v-model="onlyNugas" type="checkbox" class="check" />Cocok nugas</label>
        <label class="search">
          <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5E5A66" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></svg>
          <input v-model="search" placeholder="Cari kafe atau daerah…" aria-label="Cari kafe" />
        </label>
      </div>

      <section v-if="shown.length" class="grid">
        <CafeCard v-for="c in shown" :key="c.id" :cafe="c" />
      </section>
      <p v-else class="no-result hand">Tidak ada kafe yang cocok dengan filter ini.</p>
    </template>

    <CafeFormModal v-if="showForm" @close="showForm = false" @saved="onSaved" />
  </div>
</template>

<style scoped>
.state { position: relative; max-width: 580px; margin: 40px auto; padding: 40px 52px; display: flex; flex-direction: column; align-items: center; gap: 16px; text-align: center; }
.state h1 { font-size: 44px; line-height: 1.05; }
.state h2 { font-size: 34px; }
.state p { margin: 0; color: var(--muted); font-size: 16px; }
.empty-icon {
  width: 96px; height: 96px; border-radius: 50%; background: var(--yellow); border: var(--border);
  display: flex; align-items: center; justify-content: center; transform: rotate(-8deg);
}
.top-row { display: flex; gap: 28px; flex-wrap: wrap; }
.fav {
  position: relative; flex: 1.55 1 520px; padding: 22px 26px; display: flex; gap: 24px; align-items: center; cursor: pointer;
}
.fav:focus-visible { outline: 3px solid var(--navy); outline-offset: 3px; }
.fav-img {
  width: 150px; height: 170px; flex-shrink: 0; border: var(--border); border-radius: 16px; box-shadow: 5px 5px 0 var(--ink);
  display: flex; align-items: center; justify-content: center; transform: rotate(-3deg);
}
.fav-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 8px; }
.fav-info h1 { font-size: 42px; line-height: 1; }
.meta { font-weight: 700; font-size: 14px; color: #3d3a45; }
.row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.menu { font-size: 19px; font-weight: 400; color: #3d3a45; }
.stickies { flex: 1 1 320px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.toolbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 6px; }
.toolbar h2 { font-size: 30px; margin-right: 8px; }
.tabs { display: flex; gap: 6px; flex-wrap: wrap; }
.check { width: 18px; height: 18px; accent-color: var(--navy); }
.search {
  margin-left: auto; display: flex; align-items: center; gap: 8px; background: var(--paper); border: var(--border);
  border-radius: 12px; padding: 0 14px; height: 44px; width: 260px; max-width: 100%;
}
.search input { border: 0; outline: none; background: transparent; font: inherit; font-size: 14px; width: 100%; }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
.no-result { text-align: center; font-size: 22px; color: var(--muted); margin: 24px 0; }
@media (max-width: 720px) {
  .fav { flex-direction: column; align-items: flex-start; }
  .fav-img { width: 100%; height: 120px; }
  .search { margin-left: 0; width: 100%; }
}
</style>
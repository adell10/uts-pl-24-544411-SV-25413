<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '../components/AppHeader.vue'
import CafeFormModal from '../components/CafeFormModal.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import CupIcon from '../components/CupIcon.vue'
import StarRating from '../components/StarRating.vue'
import { api, errorMessage } from '../lib/api'
import { toast } from '../lib/toast'
import { COLORS, FACILITIES, formatDate, priceText } from '../lib/cafe'

const props = defineProps({ id: { type: String, required: true } })
const router = useRouter()

const cafe = ref(null)
const loading = ref(true)
const notFound = ref(false)
const showEdit = ref(false)
const showDelete = ref(false)
const deleting = ref(false)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/cafes/${props.id}`)   // GET /api/cafes/{id}
    cafe.value = data.data
  } catch (error) {
    if (error.response?.status === 404) notFound.value = true
    else if (error.response?.status !== 401) toast.error(errorMessage(error))
  } finally {
    loading.value = false
  }
}
onMounted(load)

function onSaved(updated) {
  cafe.value = updated
  showEdit.value = false
}

async function remove() {
  deleting.value = true
  try {
    await api.delete(`/cafes/${props.id}`)                 // DELETE /api/cafes/{id}
    toast.success('Kafe dihapus.')
    router.push({ name: 'home' })
  } catch (error) {
    if (error.response?.status !== 401) toast.error(errorMessage(error))
  } finally {
    deleting.value = false
    showDelete.value = false
  }
}
</script>

<template>
  <div class="page">
    <AppHeader :show-add="false" />

    <RouterLink to="/" class="back">
      <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6" /></svg>
      Kembali ke daftar kafe
    </RouterLink>

    <div v-if="loading" class="spinner" role="status"><span class="sr-only">Memuat…</span></div>

    <div v-else-if="notFound" class="card missing">
      <h1 class="hand">Kafe tidak ditemukan.</h1>
      <p>Mungkin sudah dihapus.</p>
      <RouterLink to="/" class="btn btn-primary">Ke daftar kafe</RouterLink>
    </div>

    <section v-else-if="cafe" class="detail">
      <div class="card main">
        <span class="tape" style="left: 60px; transform: rotate(-3deg)" aria-hidden="true"></span>
        <div class="head">
          <div class="img" :style="{ background: COLORS[cafe.color] }" aria-hidden="true">
            <CupIcon :size="92" fill="#FFFDF8" :stroke-width="1.5" />
          </div>
          <div class="title">
            <h1 class="hand">{{ cafe.name }}</h1>
            <span class="area">
              <svg aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z" /><circle cx="12" cy="10" r="2.5" /></svg>
              {{ cafe.area }}
            </span>
            <div class="badges">
              <span v-if="cafe.work_friendly" class="badge" style="background: var(--green)">Spot nugas</span>
              <span class="badge" :style="{ background: cafe.status === 'visited' ? 'var(--bg)' : 'var(--yellow)' }">
                {{ cafe.status === 'visited' ? 'Sudah mampir' : 'Mau dicoba' }}
              </span>
            </div>
          </div>
        </div>

        <div class="info">
          <div><span class="label">Rating</span><StarRating :value="cafe.rating" :size="18" /></div>
          <div><span class="label">Harga</span><span class="hand val">{{ priceText(cafe.price_level) }}</span></div>
          <div><span class="label">Terakhir mampir</span><span class="hand val">{{ formatDate(cafe.last_visited_at) }}</span></div>
          <div><span class="label">Jumlah mampir</span><span class="hand val">{{ cafe.visit_count }} kali</span></div>
        </div>

        <div v-if="cafe.favorite_menu" class="menu">
          <span class="label">Menu andalan</span>
          <span class="hand">{{ cafe.favorite_menu }}</span>
        </div>

        <div class="actions">
          <button class="btn btn-primary" @click="showEdit = true">
            <svg aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z" /></svg>
            Edit kafe
          </button>
          <button class="btn btn-danger-outline" @click="showDelete = true">
            <svg aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" /></svg>
            Hapus
          </button>
        </div>
        <span v-if="cafe.rating === 5" class="stamp" style="right: 28px; top: 26px; font-size: 24px">FAVORIT!</span>
      </div>

      <div class="side">
        <div class="notes">
          <span class="label">Catatanku</span>
          <p class="hand">{{ cafe.notes || 'Belum ada catatan.' }}</p>
        </div>
        <div class="fac card">
          <span class="label" style="color: var(--ink)">Fasilitas</span>
          <div class="fac-list">
            <span v-for="(label, key) in FACILITIES" :key="key" class="fac-item" :class="{ off: !cafe.facilities.includes(key) }">
              {{ label }}<span class="sr-only">{{ cafe.facilities.includes(key) ? ' (ada)' : ' (tidak ada)' }}</span>
            </span>
          </div>
        </div>
      </div>
    </section>

    <CafeFormModal v-if="showEdit" :cafe="cafe" @close="showEdit = false" @saved="onSaved" />
    <ConfirmModal
      v-if="showDelete"
      :title="`Hapus ${cafe.name}?`"
      message="Catatan, rating, dan riwayat mampirnya ikut hilang. Ini tidak bisa dibatalkan."
      :busy="deleting"
      @cancel="showDelete = false"
      @confirm="remove"
    />
  </div>
</template>

<style scoped>
.back { display: flex; align-items: center; gap: 6px; font-weight: 800; font-size: 14px; text-decoration: none; color: #3d4a66; width: fit-content; }
.missing { max-width: 520px; margin: 40px auto; padding: 36px; display: flex; flex-direction: column; align-items: center; gap: 12px; text-align: center; }
.missing p { margin: 0; color: var(--muted); }
.detail { display: flex; gap: 28px; flex-wrap: wrap; align-items: stretch; }
.main { position: relative; flex: 1.6 1 560px; border-radius: 22px; padding: 28px 30px; display: flex; flex-direction: column; gap: 20px; }
.head { display: flex; gap: 26px; align-items: center; flex-wrap: wrap; }
.img {
  width: 170px; height: 170px; flex-shrink: 0; border: var(--border); border-radius: 20px; box-shadow: 5px 5px 0 var(--ink);
  display: flex; align-items: center; justify-content: center; transform: rotate(-3deg);
}
.title { display: flex; flex-direction: column; gap: 8px; min-width: 0; }
.title h1 { font-size: 54px; line-height: 1; padding-right: 110px; }
.area { display: flex; align-items: center; gap: 6px; font-weight: 700; font-size: 15px; color: #3d3a45; }
.badges { display: flex; gap: 8px; margin-top: 4px; flex-wrap: wrap; }
.info { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); border: 2px solid var(--ink); border-radius: 12px; overflow: hidden; }
.info > div { padding: 10px 14px; border-right: 2px solid var(--ink); display: flex; flex-direction: column; gap: 2px; }
.info > div:last-child { border-right: 0; }
.info .label { font-size: 11px; letter-spacing: 1px; }
.val { font-size: 21px; }
.menu { display: flex; flex-direction: column; gap: 6px; }
.menu .hand { font-size: 26px; }
.actions { display: flex; gap: 10px; margin-top: auto; flex-wrap: wrap; }
.side { flex: 1 1 320px; display: flex; flex-direction: column; gap: 22px; }
.notes {
  flex: 1; min-height: 220px; background-color: var(--paper);
  background-image: repeating-linear-gradient(180deg, transparent 0, transparent 33px, #e1e5ee 33px, #e1e5ee 35px);
  background-position: 0 58px; border: var(--border); border-radius: 6px; box-shadow: 6px 6px 0 var(--ink);
  padding: 18px 22px; transform: rotate(1.5deg); display: flex; flex-direction: column; gap: 8px;
}
.notes p { margin: 0; font-size: 21px; font-weight: 400; line-height: 35px; white-space: pre-line; }
.fac { background: var(--yellow); box-shadow: 5px 5px 0 var(--ink); padding: 16px 20px; display: flex; flex-direction: column; gap: 10px; }
.fac-list { display: flex; gap: 8px; flex-wrap: wrap; }
.fac-item { font-size: 13px; font-weight: 800; padding: 6px 12px; border-radius: 10px; background: var(--paper); border: 2px solid var(--ink); }
.fac-item.off { background: rgba(255, 253, 248, .4); border-style: dashed; color: #4a4652; text-decoration: line-through; }
@media (max-width: 720px) {
  .info { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .info > div:nth-child(2) { border-right: 0; }
  .info > div:nth-child(-n + 2) { border-bottom: 2px solid var(--ink); }
  .title h1 { font-size: 40px; padding-right: 0; }
  .img { width: 120px; height: 120px; }
}
</style>
<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { api, errorMessage, fieldErrors } from '../lib/api'
import { toast } from '../lib/toast'
import { COLORS, COLOR_LABELS, FACILITIES, PRICE_LABELS, todayISO } from '../lib/cafe'

// cafe = null → mode tambah, berisi objek → mode edit
const props = defineProps({ cafe: { type: Object, default: null } })
const emit = defineEmits(['saved', 'close'])

const isEdit = computed(() => Boolean(props.cafe))

const form = reactive({
  name: props.cafe?.name ?? '',
  area: props.cafe?.area ?? '',
  status: props.cafe?.status ?? 'wishlist',
  price_level: props.cafe?.price_level ?? 2,
  rating: props.cafe?.rating ?? null,
  work_friendly: props.cafe?.work_friendly ?? false,
  facilities: [...(props.cafe?.facilities ?? [])],
  favorite_menu: props.cafe?.favorite_menu ?? '',
  notes: props.cafe?.notes ?? '',
  last_visited_at: props.cafe?.last_visited_at ?? '',
  visit_count: props.cafe?.visit_count ?? 0,
  color: props.cafe?.color ?? 'pink',
})

const errors = ref({})
const saving = ref(false)
const nameInput = ref(null)

function setStatus(status) {
  form.status = status
  if (status === 'visited' && form.visit_count < 1) form.visit_count = 1
  if (status === 'visited' && !form.last_visited_at) form.last_visited_at = todayISO()
}

function setRating(n) {
  form.rating = form.rating === n ? null : n
}

async function submit() {
  errors.value = {}
  saving.value = true

  const visited = form.status === 'visited'
  const payload = {
    ...form,
    rating: visited ? form.rating : null,
    last_visited_at: visited && form.last_visited_at ? form.last_visited_at : null,
    visit_count: visited ? Number(form.visit_count) || 0 : 0,
    favorite_menu: form.favorite_menu || null,
    notes: form.notes || null,
  }

  try {
    const { data } = isEdit.value
      ? await api.put(`/cafes/${props.cafe.id}`, payload)
      : await api.post('/cafes', payload)
    toast.success(isEdit.value ? 'Kafe diperbarui!' : 'Kafe tersimpan!')
    emit('saved', data.data)
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = fieldErrors(error)
    } else if (error.response?.status !== 401) {
      toast.error(errorMessage(error))
    }
  } finally {
    saving.value = false
  }
}

const onKey = (e) => { if (e.key === 'Escape' && !saving.value) emit('close') }
onMounted(() => { nameInput.value?.focus(); window.addEventListener('keydown', onKey) })
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))
</script>

<template>
  <div class="overlay" @click.self="!saving && emit('close')">
    <form class="card modal" role="dialog" aria-modal="true" aria-labelledby="form-title" novalidate @submit.prevent="submit">
      <span class="tape" style="left: 60px" aria-hidden="true"></span>
      <div class="modal-head">
        <h2 id="form-title" class="hand" style="font-size: 34px">{{ isEdit ? 'Edit kafe' : 'Kafe baru' }}</h2>
        <button type="button" class="icon-btn" aria-label="Tutup" @click="emit('close')">
          <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
        </button>
      </div>

      <div class="grid">
        <div class="field" :class="{ 'has-error': errors.name }">
          <label for="f-name">Nama kafe</label>
          <input id="f-name" ref="nameInput" v-model.trim="form.name" placeholder="mis. Ruang Seduh" maxlength="100" />
          <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
        </div>
        <div class="field" :class="{ 'has-error': errors.area }">
          <label for="f-area">Daerah</label>
          <input id="f-area" v-model.trim="form.area" placeholder="mis. Seturan" maxlength="100" />
          <span v-if="errors.area" class="error-text">{{ errors.area }}</span>
        </div>

        <div class="field">
          <span class="legend" id="lg-status">Status</span>
          <div class="segmented" role="group" aria-labelledby="lg-status">
            <button type="button" :aria-pressed="form.status === 'visited'" @click="setStatus('visited')">Sudah mampir</button>
            <button type="button" :aria-pressed="form.status === 'wishlist'" @click="setStatus('wishlist')">Mau dicoba</button>
          </div>
        </div>
        <div class="field">
          <span class="legend" id="lg-price">Kisaran harga <span class="opt">(per orang)</span></span>
          <div class="segmented" role="group" aria-labelledby="lg-price">
            <button v-for="n in 3" :key="n" type="button" :aria-pressed="form.price_level === n" @click="form.price_level = n">
              {{ PRICE_LABELS[n] }}
            </button>
          </div>
        </div>

        <template v-if="form.status === 'visited'">
          <div class="field" :class="{ 'has-error': errors.rating }">
            <span class="legend" id="lg-rating">Rating</span>
            <div class="rating" role="group" aria-labelledby="lg-rating">
              <button
                v-for="n in 5" :key="n" type="button" class="star-btn" :class="{ on: form.rating && n <= form.rating }"
                :aria-label="`${n} bintang`" :aria-pressed="form.rating === n" @click="setRating(n)"
              >★</button>
            </div>
            <span v-if="errors.rating" class="error-text">{{ errors.rating }}</span>
          </div>
          <div class="field-row">
            <div class="field" :class="{ 'has-error': errors.last_visited_at }">
              <label for="f-last">Terakhir mampir</label>
              <input id="f-last" v-model="form.last_visited_at" type="date" :max="todayISO()" />
              <span v-if="errors.last_visited_at" class="error-text">{{ errors.last_visited_at }}</span>
            </div>
            <div class="field" :class="{ 'has-error': errors.visit_count }">
              <label for="f-count">Jumlah mampir</label>
              <input id="f-count" v-model.number="form.visit_count" type="number" min="0" max="9999" />
            </div>
          </div>
        </template>

        <div class="field">
          <label for="f-menu">Menu andalan <span class="opt">(opsional)</span></label>
          <input id="f-menu" v-model.trim="form.favorite_menu" placeholder="mis. Es kopi susu gula aren" maxlength="100" />
        </div>
        <div class="field">
          <span class="legend" id="lg-color">Warna kartu</span>
          <div class="swatches" role="group" aria-labelledby="lg-color">
            <button
              v-for="(hex, key) in COLORS" :key="key" type="button" class="swatch" :class="{ selected: form.color === key }"
              :style="{ background: hex }" :aria-label="COLOR_LABELS[key]" :aria-pressed="form.color === key" @click="form.color = key"
            ></button>
          </div>
        </div>

        <fieldset class="field full">
          <legend class="legend">Fasilitas</legend>
          <div class="facilities">
            <label v-for="(label, key) in FACILITIES" :key="key" class="facility" :class="{ on: form.facilities.includes(key) }">
              <input v-model="form.facilities" type="checkbox" :value="key" />
              {{ label }}
            </label>
          </div>
          <span v-if="errors['facilities.0'] || errors.facilities" class="error-text">{{ errors['facilities.0'] || errors.facilities }}</span>
        </fieldset>
      </div>

      <label class="nugas">
        <input v-model="form.work_friendly" type="checkbox" />
        Cocok buat nugas <span class="opt">(tenang, wifi stabil, betah lama)</span>
      </label>

      <div class="field">
        <label for="f-notes">Catatan <span class="opt">(opsional)</span></label>
        <textarea id="f-notes" v-model="form.notes" rows="2" maxlength="2000" placeholder="Kesan, tips, jam ramai…"></textarea>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn" :disabled="saving" @click="emit('close')">Batal</button>
        <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'Menyimpan…' : 'Simpan kafe' }}</button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px 20px; }
.full { grid-column: 1 / -1; }
fieldset.field { border: 0; margin: 0; padding: 0; }
fieldset .legend { padding: 0; margin-bottom: 6px; }
.field-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.rating { display: flex; gap: 4px; }
.star-btn {
  width: 44px; height: 44px; border: 2px solid #c9c3b6; border-radius: 10px; background: var(--white);
  font-size: 22px; color: #c9c3b6; cursor: pointer;
}
.star-btn.on { border-color: var(--ink); color: var(--star); background: #fff6dc; }
.swatches { display: flex; gap: 8px; height: 46px; align-items: center; }
.swatch { width: 40px; height: 40px; border-radius: 10px; border: 2px solid var(--ink); cursor: pointer; }
.swatch.selected { box-shadow: 0 0 0 3px var(--paper), 0 0 0 5.5px var(--ink); }
.facilities { display: flex; gap: 8px; flex-wrap: wrap; }
.facility {
  display: flex; align-items: center; gap: 6px; height: 40px; padding: 0 12px; border: 2px solid var(--ink);
  border-radius: 10px; background: var(--white); font-weight: 800; font-size: 13px; cursor: pointer;
}
.facility.on { background: var(--yellow); }
.facility input { width: 16px; height: 16px; accent-color: var(--ink); }
.nugas {
  display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 14px; background: #eef2fa;
  border: 2px dashed var(--navy); border-radius: 12px; padding: 10px 14px; cursor: pointer; flex-wrap: wrap;
}
.nugas input { width: 20px; height: 20px; accent-color: var(--navy); }
.opt { font-weight: 600; color: var(--muted); }
@media (max-width: 720px) {
  .grid { grid-template-columns: 1fr; }
}
</style>
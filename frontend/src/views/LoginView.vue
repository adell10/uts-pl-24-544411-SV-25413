<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import CupIcon from '../components/CupIcon.vue'
import { api, errorMessage, fieldErrors } from '../lib/api'
import { auth } from '../lib/auth'
import { toast } from '../lib/toast'

const router = useRouter()
const form = reactive({ email: '', password: '' })
const errors = ref({})
const message = ref('')
const loading = ref(false)
const showPassword = ref(false)

async function login() {
  errors.value = {}
  message.value = ''
  loading.value = true
  try {
    const { data } = await api.post('/login', form)
    auth.setSession(data.token, data.user)          // simpan token
    toast.success(`Halo, ${data.user.name}!`)
    router.push({ name: 'home' })
  } catch (error) {
    if (error.response?.status === 422) errors.value = fieldErrors(error)
    else message.value = errorMessage(error)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="login">
    <section class="intro">
      <div class="brand hand">
        <span class="tile"><CupIcon :size="24" /></span>
        cafe hopper<span style="color: var(--navy)">.</span>
      </div>
      <h1 class="hand">Catat tiap kafe<br />yang kamu singgahi.</h1>
      <div class="polaroids" aria-hidden="true">
        <div class="polaroid" style="left: 0; top: 30px; transform: rotate(-6deg)">
          <div class="photo" style="background: var(--pink)"><CupIcon :size="70" fill="#FFFDF8" stroke="#23212B" :stroke-width="1.6" /></div>
          <span class="hand">Ruang Seduh ★5</span>
        </div>
        <div class="polaroid" style="left: 175px; top: 0; transform: rotate(4deg)">
          <div class="photo" style="background: var(--green)"><CupIcon :size="70" fill="#FFFDF8" stroke="#23212B" :stroke-width="1.6" /></div>
          <span class="hand">Teras Pagi ★4</span>
        </div>
        <div class="note hand">Next: Lantai Dua, Kotabaru!</div>
      </div>
    </section>

    <form class="card login-card" novalidate @submit.prevent="login">
      <span class="tape" style="left: 150px; transform: rotate(-3deg)" aria-hidden="true"></span>
      <div>
        <h2 class="hand" style="font-size: 38px">Masuk</h2>
        <p class="sub">Lanjut catat petualangan ngopimu.</p>
      </div>

      <div class="field" :class="{ 'has-error': errors.email || message }">
        <label for="email">Email</label>
        <input id="email" v-model.trim="form.email" type="email" autocomplete="username" placeholder="kamu@email.com" />
        <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
      </div>

      <div class="field" :class="{ 'has-error': errors.password || message }">
        <label for="password">Password</label>
        <div class="pw">
          <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" />
          <button type="button" class="eye" :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'" @click="showPassword = !showPassword">
            <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#5E5A66" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" /></svg>
          </button>
        </div>
        <span v-if="errors.password" class="error-text">{{ errors.password }}</span>
      </div>

      <p v-if="message" class="error-text" role="alert">{{ message }}</p>

      <button type="submit" class="btn btn-primary btn-block" :disabled="loading" style="height: 52px; font-size: 22px">
        {{ loading ? 'Masuk…' : 'Masuk' }}
      </button>
      <p class="hint">Akun dibuat oleh admin.</p>
    </form>
  </main>
</template>

<style scoped>
.login {
  min-height: 100vh; max-width: 1280px; margin: 0 auto; padding: 48px;
  display: flex; gap: 48px; align-items: center;
}
.intro { flex: 1; display: flex; flex-direction: column; gap: 28px; padding-left: 24px; min-width: 0; }
.brand { font-size: 36px; display: flex; align-items: center; gap: 10px; line-height: 1; }
.tile {
  background: var(--yellow); border: var(--border); border-radius: 12px; width: 46px; height: 46px;
  display: flex; align-items: center; justify-content: center; transform: rotate(-6deg); box-shadow: 3px 3px 0 var(--ink);
}
h1 { font-size: 56px; line-height: 1.05; }
.polaroids { position: relative; height: 290px; max-width: 540px; }
.polaroid {
  position: absolute; width: 190px; background: var(--paper); border: var(--border);
  padding: 10px 10px 16px; box-shadow: 5px 5px 0 var(--ink);
}
.polaroid .photo { height: 150px; border: 2px solid var(--ink); display: flex; align-items: center; justify-content: center; }
.polaroid span { display: block; font-size: 20px; margin-top: 8px; }
.note {
  position: absolute; left: 350px; top: 40px; width: 170px; background: var(--yellow); border: var(--border);
  padding: 14px 16px; box-shadow: var(--shadow); transform: rotate(-3deg); font-size: 21px; line-height: 1.25;
}
.login-card { position: relative; width: 460px; flex-shrink: 0; padding: 40px; display: flex; flex-direction: column; gap: 18px; }
.sub { margin: 4px 0 0; font-size: 15px; color: var(--muted); }
.pw { position: relative; }
.pw input { padding-right: 52px; }
.eye { position: absolute; right: 4px; top: 1px; width: 44px; height: 44px; border: 0; background: transparent; cursor: pointer; }
.hint { margin: 0; text-align: center; font-size: 13px; color: var(--muted); }
@media (max-width: 960px) {
  .login { flex-direction: column; padding: 24px 16px; }
  .intro { padding-left: 0; align-items: center; text-align: center; }
  .polaroids { display: none; }
  h1 { font-size: 40px; }
  .login-card { width: 100%; max-width: 460px; padding: 28px 22px; }
}
</style>
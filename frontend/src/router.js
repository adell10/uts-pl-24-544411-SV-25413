import { createRouter, createWebHistory } from 'vue-router'
import { auth } from './lib/auth'
import LoginView from './views/LoginView.vue'
import HomeView from './views/HomeView.vue'
import DetailView from './views/DetailView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', name: 'login', component: LoginView, meta: { guest: true } },
    { path: '/', name: 'home', component: HomeView, meta: { auth: true } },
    { path: '/kafe/:id', name: 'detail', component: DetailView, meta: { auth: true }, props: true },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

// Halaman wajib login → lempar ke /login kalau belum punya token
router.beforeEach((to) => {
  if (to.meta.auth && !auth.isLoggedIn) return { name: 'login' }
  if (to.meta.guest && auth.isLoggedIn) return { name: 'home' }
})

export default router
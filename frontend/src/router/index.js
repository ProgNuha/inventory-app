import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import AppLayout from '../layouts/AppLayout.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', component: () => import('../views/LoginView.vue'), meta: { guest: true } },
    {
      path: '/',
      component: AppLayout,
      meta: { auth: true },
      children: [
        { path: '', redirect: '/barang-masuk' },
        { path: 'barang-masuk', component: () => import('../views/BarangMasukView.vue'), meta: { title: 'Barang Masuk' } },
        { path: 'master/kategori', component: () => import('../views/CategoryView.vue'), meta: { title: 'Kategori', admin: true } },
        { path: 'master/sub-kategori', component: () => import('../views/SubCategoryView.vue'), meta: { title: 'Sub Kategori', admin: true } },
        { path: 'users', component: () => import('../views/UserView.vue'), meta: { title: 'Manajemen User', admin: true } },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.matched.some((r) => r.meta.auth) && !auth.isLoggedIn) return '/login'
  if (to.meta.guest && auth.isLoggedIn) return '/'
  if (to.matched.some((r) => r.meta.admin) && !auth.isAdmin) return '/'
})

export default router
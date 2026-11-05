<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const sidebarOpen = ref(true)
const userMenuOpen = ref(false)
const masterOpen = ref(route.path.startsWith('/master'))

const initials = computed(() =>
  (auth.user?.name || '?').split(' ').map((w) => w[0]).slice(0, 2).join('').toUpperCase(),
)

async function logout() {
  await auth.logout()
  router.push('/login')
}

const linkClass = 'flex items-center gap-2 rounded px-3 py-2 text-sm text-slate-300 hover:bg-white/10'
const activeClass = '!bg-primary !text-white'
</script>

<template>
  <div class="flex min-h-screen">
    <aside v-show="sidebarOpen" class="w-60 shrink-0 bg-sidebar text-white">
      <div class="px-4 py-4 text-sm font-semibold border-b border-white/10">Aplikasi Pengelolaan Barang</div>

      <nav class="p-3 space-y-1">
        <RouterLink to="/barang-masuk" :class="linkClass" :active-class="activeClass">Barang Masuk</RouterLink>

        <div v-if="auth.isAdmin">
          <button :class="linkClass + ' w-full justify-between'" @click="masterOpen = !masterOpen">
            <span>Master Data</span>
            <span class="text-xs">{{ masterOpen ? '▾' : '▸' }}</span>
          </button>
          <div v-show="masterOpen" class="ml-3 mt-1 space-y-1 border-l border-white/10 pl-2">
            <RouterLink to="/master/kategori" :class="linkClass" :active-class="activeClass">Kategori</RouterLink>
            <RouterLink to="/master/sub-kategori" :class="linkClass" :active-class="activeClass">Sub Kategori</RouterLink>
          </div>
        </div>

        <RouterLink v-if="auth.isAdmin" to="/users" :class="linkClass" :active-class="activeClass">
          Manajemen User
        </RouterLink>
      </nav>
    </aside>

    <div class="flex-1 min-w-0 flex flex-col">
      <header class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-4">
        <button class="text-xl text-slate-600" @click="sidebarOpen = !sidebarOpen">☰</button>

        <div class="relative">
          <button class="flex items-center gap-2" @click="userMenuOpen = !userMenuOpen">
            <span class="h-8 w-8 rounded-full bg-primary text-white text-xs font-semibold grid place-items-center">
              {{ initials }}
            </span>
            <span class="text-left leading-tight">
              <span class="block text-xs font-medium">{{ auth.user?.name }}</span>
              <span class="block text-[11px] text-slate-500 capitalize">{{ auth.user?.role }}</span>
            </span>
          </button>
          <div v-if="userMenuOpen" class="absolute right-0 mt-2 w-36 rounded border bg-white shadow">
            <button class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-slate-50" @click="logout">
              Logout
            </button>
          </div>
        </div>
      </header>

      <main class="p-4 flex-1">
        <p class="text-[11px] uppercase tracking-wide text-slate-500">Home / {{ route.meta.title }}</p>
        <h1 class="text-lg font-semibold mb-4">{{ route.meta.title }}</h1>
        <RouterView />
      </main>
    </div>
  </div>
</template>
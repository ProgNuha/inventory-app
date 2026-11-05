<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const username = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(username.value, password.value) // <-- cocokkan dengan store-mu
    router.push('/barang-masuk')
  } catch (e) {
    error.value = e.response?.data?.message || 'Username atau password salah'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="grid min-h-screen bg-white md:grid-cols-2">
    <div class="flex items-center justify-center p-8">
      <form class="w-full max-w-sm" @submit.prevent="submit">
        <div class="mb-10">
          <p class="text-xl font-semibold text-primary">Aplikasi Pengelolaan Barang</p>
          <p class="text-xs text-slate-500">PT JMC Indonesia</p>
        </div>

        <h1 class="text-lg font-semibold text-primary">LOGIN</h1>
        <p class="mb-5 mt-1 text-xs text-slate-600">Selamat Datang, silahkan masukkan username dan password anda!</p>

        <div v-if="error" class="mb-3 rounded bg-red-50 px-3 py-2 text-xs text-red-700">{{ error }}</div>

        <input v-model="username" class="mb-3 w-full rounded bg-slate-50 px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-primary/40" placeholder="Username" autocomplete="username" required />
        <input v-model="password" type="password" class="mb-6 w-full rounded bg-slate-50 px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-primary/40" placeholder="Password" autocomplete="current-password" required />

        <button type="submit" class="btn-primary w-full !py-3 text-xs tracking-wide" :disabled="loading">
          {{ loading ? 'MEMPROSES...' : 'MASUK' }}
        </button>
      </form>
    </div>

    <div
      class="hidden bg-slate-200 bg-cover bg-center md:block"
      style="background-image: url('/login-bg.jpg')"
    ></div>
  </div>
</template>
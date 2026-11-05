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

const demoAccounts = [
  { role: 'Admin', username: 'demoadmin', password: 'DemoAdmin123' },
  { role: 'Operator', username: 'demooperator', password: 'DemoOperator123' },
  { role: 'Operator', username: 'sitirahayu', password: 'Operator12345', locked: true },
]

function useDemo(acc) {
  username.value = acc.username
  password.value = acc.password
  error.value = ''
}

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

        <div class="mt-6 rounded border border-slate-200 bg-slate-50 p-3">
          <p class="mb-2 text-[11px] font-bold uppercase tracking-wide text-slate-500">Akun Demo</p>
          <button
            v-for="acc in demoAccounts"
            :key="acc.username"
            type="button"
            class="flex w-full items-center justify-between gap-2 rounded px-2 py-1.5 text-left text-xs text-slate-700 hover:bg-white"
            title="Klik untuk mengisi otomatis"
            @click="useDemo(acc)"
          >
            <span><b class="font-semibold">{{ acc.role }}:</b> {{ acc.username }} / {{ acc.password }}</span>
            <span v-if="acc.locked" class="shrink-0 rounded bg-red-100 px-1.5 py-0.5 text-[10px] text-red-700">Terkunci</span>
            <span v-else class="shrink-0 text-[11px] text-primary">Isi otomatis</span>
          </button>
        </div>
      </form>
    </div>

    <div
      class="hidden bg-slate-200 bg-cover bg-center md:block"
      style="background-image: url('/login-bg.jpg')"
    ></div>
  </div>
</template>
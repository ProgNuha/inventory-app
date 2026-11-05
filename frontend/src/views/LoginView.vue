<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const username = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')
const errors = ref({})

async function submit() {
  error.value = ''
  errors.value = {}

  if (!username.value) errors.value.username = 'Username wajib diisi'
  if (!password.value) errors.value.password = 'Password wajib diisi'
  if (Object.keys(errors.value).length) return

  loading.value = true
  try {
    await auth.login(username.value, password.value)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Terjadi kesalahan, coba lagi'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen grid lg:grid-cols-2 bg-white">
    <div class="flex items-center justify-center p-8">
      <form class="w-full max-w-sm" @submit.prevent="submit">
        <h1 class="text-xl font-semibold text-slate-800">Aplikasi Pengelolaan Barang</h1>
        <p class="text-xs text-slate-500 mb-8">Sistem pencatatan barang masuk</p>

        <h2 class="text-primary font-semibold tracking-wide mb-1">LOGIN</h2>
        <p class="text-xs text-slate-600 mb-5">Selamat Datang, silahkan masukkan username dan password anda!</p>

        <div v-if="error" class="mb-4 rounded bg-red-50 border border-red-200 text-red-700 text-sm px-3 py-2">
          {{ error }}
        </div>

        <div class="mb-4">
          <input v-model="username" type="text" placeholder="Username" autocomplete="username"
            class="w-full rounded bg-slate-100 px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-primary/40" />
          <p v-if="errors.username" class="text-xs text-red-600 mt-1">{{ errors.username }}</p>
        </div>

        <div class="mb-6">
          <input v-model="password" type="password" placeholder="Password" autocomplete="current-password"
            class="w-full rounded bg-slate-100 px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-primary/40" />
          <p v-if="errors.password" class="text-xs text-red-600 mt-1">{{ errors.password }}</p>
        </div>

        <button type="submit" :disabled="loading"
          class="w-full rounded bg-primary hover:bg-primary-dark text-white text-sm font-medium py-2.5 shadow disabled:opacity-60">
          {{ loading ? 'Memproses...' : 'MASUK' }}
        </button>
      </form>
    </div>

    <div class="hidden lg:block bg-gradient-to-br from-primary to-sidebar"></div>
  </div>
</template>
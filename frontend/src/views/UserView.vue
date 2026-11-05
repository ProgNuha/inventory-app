<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import http from '../api/http'
import { useAuthStore } from '../stores/auth'
import Pagination from '../components/Pagination.vue'
import ModalDialog from '../components/ModalDialog.vue'

const auth = useAuthStore()

const rows = ref([])
const meta = reactive({ page: 1, lastPage: 1, from: 0 })
const search = ref('')
const role = ref('')
const loading = ref(false)

async function load(page = 1) {
  loading.value = true
  try {
    const { data } = await http.get('/users', {
      params: { page, search: search.value || undefined, role: role.value || undefined },
    })
    rows.value = data.data
    meta.page = data.current_page
    meta.lastPage = data.last_page
    meta.from = data.from ?? 0
  } finally {
    loading.value = false
  }
}

let timer
watch([search, role], () => {
  clearTimeout(timer)
  timer = setTimeout(() => load(1), 300)
})
onMounted(() => load())

// form modal
const open = ref(false)
const editingId = ref(null)
const form = reactive({ role: 'admin', username: '', password: '', name: '', email: '' })
const errors = ref({})
const formError = ref('')
const saving = ref(false)

function openForm(row = null) {
  editingId.value = row?.id ?? null
  form.role = row?.role ?? 'admin'
  form.username = row?.username ?? ''
  form.password = ''
  form.name = row?.name ?? ''
  form.email = row?.email ?? ''
  errors.value = {}
  formError.value = ''
  open.value = true
}

async function save() {
  saving.value = true
  errors.value = {}
  formError.value = ''
  const payload = { ...form, password: form.password || undefined }
  try {
    if (editingId.value) await http.put(`/users/${editingId.value}`, payload)
    else await http.post('/users', payload)
    const wasEdit = !!editingId.value
    open.value = false
    await load(wasEdit ? meta.page : 1)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
      if (!e.response.data.errors) formError.value = e.response.data.message
    } else {
      formError.value = 'Terjadi kesalahan, coba lagi'
    }
  } finally {
    saving.value = false
  }
}

async function toggleLock(row) {
  const action = row.is_locked ? 'membuka kunci' : 'mengunci'
  if (!confirm(`Yakin ingin ${action} user "${row.username}"?`)) return
  try {
    await http.patch(`/users/${row.id}/lock`)
    await load(meta.page)
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal mengubah status kunci')
  }
}

async function remove(row) {
  if (!confirm(`Hapus user "${row.username}"?`)) return
  try {
    await http.delete(`/users/${row.id}`)
    await load(rows.value.length === 1 && meta.page > 1 ? meta.page - 1 : meta.page)
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menghapus data')
  }
}
</script>

<template>
  <div class="rounded bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-2 p-3">
      <button class="btn-primary" @click="openForm()">+ Tambah Data</button>
      <div class="flex gap-2">
        <select v-model="role" class="input !w-36">
          <option value="">Semua Role</option>
          <option value="admin">Admin</option>
          <option value="operator">Operator</option>
        </select>
        <input v-model="search" class="input !w-56" placeholder="Cari username / nama..." />
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
          <tr>
            <th class="px-3 py-2 text-left w-12">No</th>
            <th class="px-3 py-2 text-left w-44">Action</th>
            <th class="px-3 py-2 text-left">Username</th>
            <th class="px-3 py-2 text-left">Nama</th>
            <th class="px-3 py-2 text-left">Email</th>
            <th class="px-3 py-2 text-left">Role</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!loading && !rows.length">
            <td colspan="6" class="px-3 py-8 text-center text-slate-400">Tidak ada data</td>
          </tr>
          <tr v-for="(row, i) in rows" :key="row.id" class="border-t">
            <td class="px-3 py-2">{{ meta.from + i }}</td>
            <td class="px-3 py-2 space-x-3">
              <button class="text-primary hover:underline" @click="openForm(row)">Edit</button>
              <template v-if="row.id !== auth.user?.id">
                <button class="text-amber-600 hover:underline" @click="toggleLock(row)">
                  {{ row.is_locked ? 'Buka Kunci' : 'Kunci' }}
                </button>
                <button class="text-red-600 hover:underline" @click="remove(row)">Hapus</button>
              </template>
            </td>
            <td class="px-3 py-2">
              {{ row.username }}
              <span v-if="row.is_locked" class="ml-1 rounded bg-red-100 px-1.5 py-0.5 text-[10px] text-red-700">Terkunci</span>
            </td>
            <td class="px-3 py-2">{{ row.name }}</td>
            <td class="px-3 py-2">{{ row.email }}</td>
            <td class="px-3 py-2 capitalize">{{ row.role }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="p-3">
      <Pagination :page="meta.page" :last-page="meta.lastPage" @change="load" />
    </div>
  </div>

  <ModalDialog v-model="open" title="Form">
    <form id="user-form" @submit.prevent="save">
      <p v-if="formError" class="mb-3 rounded bg-red-50 px-3 py-2 text-sm text-red-700">{{ formError }}</p>

      <div class="mb-3">
        <label class="label">Role</label>
        <select v-model="form.role" class="input">
          <option value="admin">Admin</option>
          <option value="operator">Operator</option>
        </select>
        <p v-if="errors.role" class="err">{{ errors.role[0] }}</p>
      </div>

      <div class="mb-3">
        <label class="label">Username</label>
        <input v-model="form.username" maxlength="100" autocomplete="off" class="input" />
        <p v-if="errors.username" class="err">{{ errors.username[0] }}</p>
      </div>

      <div class="mb-3">
        <label class="label">Password</label>
        <input v-model="form.password" type="password" maxlength="100" autocomplete="new-password" class="input"
          :placeholder="editingId ? 'Kosongkan jika tidak diubah' : ''" />
        <p v-if="errors.password" class="err">{{ errors.password[0] }}</p>
      </div>

      <div class="mb-3">
        <label class="label">Nama</label>
        <input v-model="form.name" maxlength="100" class="input" />
        <p v-if="errors.name" class="err">{{ errors.name[0] }}</p>
      </div>

      <div>
        <label class="label">Email</label>
        <input v-model="form.email" maxlength="100" class="input" />
        <p v-if="errors.email" class="err">{{ errors.email[0] }}</p>
      </div>
    </form>

    <template #footer>
      <button type="button" class="btn-secondary" @click="open = false">Close</button>
      <button type="submit" form="user-form" class="btn-primary" :disabled="saving">
        {{ saving ? 'Menyimpan...' : 'Submit' }}
      </button>
    </template>
  </ModalDialog>
</template>
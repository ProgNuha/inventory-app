<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import http from '../api/http'
import { confirmDialog, confirmDelete, showError } from '../utils/alert'
import { useAuthStore } from '../stores/auth'
import Pagination from '../components/Pagination.vue'
import LoadingOverlay from '../components/LoadingOverlay.vue'
import FlashBanner from '../components/FlashBanner.vue'
import { useFlash } from '../composables/useFlash'
import ModalDialog from '../components/ModalDialog.vue'
import SearchSelect from '../components/SearchSelect.vue'
import IconButton from '../components/IconButton.vue'

const auth = useAuthStore()

const rows = ref([])
const meta = reactive({ page: 1, lastPage: 1, from: 0 })
const search = ref('')
const role = ref('')
const loading = ref(true)
const flash = useFlash()
const busyId = ref(null) // id baris yang sedang dihapus / dikunci
const busyAction = ref('') // 'delete' | 'lock', agar hanya tombol yang diklik yang berspinner
const busy = computed(() => busyId.value !== null) // selama ada proses, tombol aksi lain dikunci
const roleOptions = [
  { value: 'admin', label: 'Admin' },
  { value: 'operator', label: 'Operator' },
]

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
  loading.value = true
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
    flash.show(wasEdit ? 'User berhasil diperbarui' : 'User berhasil ditambahkan')
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
  if (busy.value) return
  const wasLocked = row.is_locked
  const ok = await confirmDialog({
    title: wasLocked ? 'Buka kunci akun?' : 'Kunci akun?',
    text: wasLocked
      ? `User "${row.username}" dapat login kembali.`
      : `User "${row.username}" tidak dapat login dan sesinya akan berakhir.`,
    confirmText: wasLocked ? 'Ya, buka kunci' : 'Ya, kunci',
    danger: !wasLocked,
  })
  if (!ok) return
  busyId.value = row.id
  busyAction.value = 'lock'
  try {
    await http.patch(`/users/${row.id}/lock`)
    await load(meta.page)
    flash.show(wasLocked ? `Kunci user "${row.username}" dibuka` : `User "${row.username}" dikunci`)
  } catch (e) {
    showError(e.response?.data?.message || 'Gagal mengubah status kunci', 'Gagal mengubah status')
  } finally {
    busyId.value = null
  }
}

async function remove(row) {
  if (busy.value) return
  if (!(await confirmDelete(`User "${row.username}"`))) return
  busyId.value = row.id
  busyAction.value = 'delete'
  try {
    await http.delete(`/users/${row.id}`)
    await load(rows.value.length === 1 && meta.page > 1 ? meta.page - 1 : meta.page)
    flash.show(`User "${row.username}" berhasil dihapus`)
  } catch (e) {
    showError(e.response?.data?.message || 'Gagal menghapus data', 'Gagal menghapus')
  } finally {
    busyId.value = null
  }
}
</script>

<template>
  <div class="rounded bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-2 p-3">
      <button class="btn-primary" @click="openForm()">+ Tambah Data</button>
      <div class="flex gap-2">
        <SearchSelect v-model="role" :options="roleOptions" label-key="label" value-key="value" empty-label="Semua Role" class="!w-36" />
        <input v-model="search" class="input !w-56" placeholder="Cari username / nama..." />
      </div>
    </div>

    <FlashBanner :message="flash.message.value" @close="flash.hide" />

    <div class="relative">
      <LoadingOverlay :show="loading && rows.length > 0" />
    <div class="overflow-x-auto transition-opacity" :class="{ 'pointer-events-none opacity-50': loading && rows.length }">
      <table class="data-table">
        <thead>
          <tr>
            <th class="w-12">No</th>
            <th class="w-32">Action</th>
            <th>Username</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!rows.length">
            <td colspan="6" class="py-8 text-center text-slate-500">
              {{ loading ? 'Memuat data...' : 'Tidak ada data' }}
            </td>
          </tr>
          <tr v-for="(row, i) in rows" :key="row.id">
            <td>{{ meta.from + i }}</td>
            <td class="whitespace-nowrap !px-1">
              <IconButton :disabled="busy" icon="edit" title="Edit" color="text-primary hover:bg-slate-100" @click="openForm(row)" />
              <template v-if="row.id !== auth.user?.id">
                <IconButton :disabled="busy" :icon="row.is_locked ? 'unlock' : 'lock'" :title="row.is_locked ? 'Buka kunci' : 'Kunci'" :loading="busyId === row.id && busyAction === 'lock'" @click="toggleLock(row)" />
                <IconButton :disabled="busy" icon="trash" title="Hapus" color="text-red-500 hover:bg-red-50" :loading="busyId === row.id && busyAction === 'delete'" @click="remove(row)" />
              </template>
            </td>
            <td>
              {{ row.username }}
              <span v-if="row.is_locked" class="ml-1 rounded bg-red-100 px-1.5 py-0.5 text-[10px] text-red-700">Terkunci</span>
            </td>
            <td>{{ row.name }}</td>
            <td>{{ row.email }}</td>
            <td class="capitalize">{{ row.role }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    </div>

    <div class="border-t bg-slate-50 p-3">
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
<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import http from '../api/http'
import Pagination from '../components/Pagination.vue'
import ModalDialog from '../components/ModalDialog.vue'

const rows = ref([])
const meta = reactive({ page: 1, lastPage: 1, from: 0 })
const search = ref('')
const loading = ref(false)

async function load(page = 1) {
  loading.value = true
  try {
    const { data } = await http.get('/categories', {
      params: { page, search: search.value || undefined },
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
watch(search, () => {
  clearTimeout(timer)
  timer = setTimeout(() => load(1), 400)
})
onMounted(() => load())

// form modal
const open = ref(false)
const editingId = ref(null)
const form = reactive({ kode: '', nama: '' })
const errors = ref({})
const formError = ref('')
const saving = ref(false)

function openForm(row = null) {
  editingId.value = row?.id ?? null
  form.kode = row?.kode ?? ''
  form.nama = row?.nama ?? ''
  errors.value = {}
  formError.value = ''
  open.value = true
}

async function save() {
  saving.value = true
  errors.value = {}
  formError.value = ''
  try {
    if (editingId.value) await http.put(`/categories/${editingId.value}`, form)
    else await http.post('/categories', form)
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

async function remove(row) {
  if (!confirm(`Hapus kategori "${row.nama}"?`)) return
  try {
    await http.delete(`/categories/${row.id}`)
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
      <input v-model="search" class="input !w-56" placeholder="Cari kode / nama..." />
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
          <tr>
            <th class="px-3 py-2 text-left w-12">No</th>
            <th class="px-3 py-2 text-left w-28">Action</th>
            <th class="px-3 py-2 text-left">Kode</th>
            <th class="px-3 py-2 text-left">Nama Kategori</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!loading && !rows.length">
            <td colspan="4" class="px-3 py-8 text-center text-slate-400">Tidak ada data</td>
          </tr>
          <tr v-for="(row, i) in rows" :key="row.id" class="border-t">
            <td class="px-3 py-2">{{ meta.from + i }}</td>
            <td class="px-3 py-2 space-x-3">
              <IconButton icon="edit" title="Edit" color="text-primary hover:bg-slate-100" @click="openEdit(row)" />
              <IconButton icon="trash" title="Hapus" color="text-red-600 hover:bg-red-50" @click="remove(row)" />
            </td>
            <td class="px-3 py-2">{{ row.kode }}</td>
            <td class="px-3 py-2">{{ row.nama }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="p-3">
      <Pagination :page="meta.page" :last-page="meta.lastPage" @change="load" />
    </div>
  </div>

  <ModalDialog v-model="open" title="Form">
    <form id="category-form" @submit.prevent="save">
      <p v-if="formError" class="mb-3 rounded bg-red-50 px-3 py-2 text-sm text-red-700">{{ formError }}</p>

      <div class="mb-3">
        <label class="label">Kode Kategori</label>
        <input v-model="form.kode" maxlength="10" class="input !w-40" />
        <p v-if="errors.kode" class="err">{{ errors.kode[0] }}</p>
      </div>

      <div>
        <label class="label">Nama Kategori</label>
        <input v-model="form.nama" maxlength="100" class="input" />
        <p v-if="errors.nama" class="err">{{ errors.nama[0] }}</p>
      </div>
    </form>

    <template #footer>
      <button type="button" class="btn-secondary" @click="open = false">Close</button>
      <button type="submit" form="category-form" class="btn-primary" :disabled="saving">
        {{ saving ? 'Menyimpan...' : 'Submit' }}
      </button>
    </template>
  </ModalDialog>
</template>
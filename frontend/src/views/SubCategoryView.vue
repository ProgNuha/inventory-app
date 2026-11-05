<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import http from '../api/http'
import { rupiah } from '../utils/format'
import Pagination from '../components/Pagination.vue'
import ModalDialog from '../components/ModalDialog.vue'
import RupiahInput from '../components/RupiahInput.vue'

const rows = ref([])
const meta = reactive({ page: 1, lastPage: 1, from: 0 })
const search = ref('')
const loading = ref(false)
const categories = ref([])

async function load(page = 1) {
  loading.value = true
  try {
    const { data } = await http.get('/sub-categories', {
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

async function loadCategories() {
  const { data } = await http.get('/options/categories')
  categories.value = data
}

let timer
watch(search, () => {
  clearTimeout(timer)
  timer = setTimeout(() => load(1), 400)
})
onMounted(() => {
  load()
  loadCategories()
})

// form modal
const open = ref(false)
const editingId = ref(null)
const form = reactive({ category_id: '', nama: '', batas_harga: '' })
const errors = ref({})
const formError = ref('')
const saving = ref(false)

function openForm(row = null) {
  editingId.value = row?.id ?? null
  form.category_id = row?.category_id ?? ''
  form.nama = row?.nama ?? ''
  form.batas_harga = row?.batas_harga ?? ''
  errors.value = {}
  formError.value = ''
  open.value = true
}

async function save() {
  saving.value = true
  errors.value = {}
  formError.value = ''
  try {
    if (editingId.value) await http.put(`/sub-categories/${editingId.value}`, form)
    else await http.post('/sub-categories', form)
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
  if (!confirm(`Hapus sub kategori "${row.nama}"?`)) return
  try {
    await http.delete(`/sub-categories/${row.id}`)
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
      <input v-model="search" class="input !w-64" placeholder="Cari kategori / sub kategori..." />
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
          <tr>
            <th class="px-3 py-2 text-left w-12">No</th>
            <th class="px-3 py-2 text-left w-28">Action</th>
            <th class="px-3 py-2 text-left">Kategori Barang</th>
            <th class="px-3 py-2 text-left">Sub Kategori Barang</th>
            <th class="px-3 py-2 text-right">Batas Harga (Rp)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!loading && !rows.length">
            <td colspan="5" class="px-3 py-8 text-center text-slate-400">Tidak ada data</td>
          </tr>
          <tr v-for="(row, i) in rows" :key="row.id" class="border-t">
            <td class="px-3 py-2">{{ meta.from + i }}</td>
            <td class="px-3 py-2 space-x-3">
              <IconButton icon="edit" title="Edit" color="text-primary hover:bg-slate-100" @click="openEdit(row)" />
              <IconButton icon="trash" title="Hapus" color="text-red-600 hover:bg-red-50" @click="remove(row)" />
            </td>
            <td class="px-3 py-2">{{ row.category?.nama }}</td>
            <td class="px-3 py-2">{{ row.nama }}</td>
            <td class="px-3 py-2 text-right">{{ rupiah(row.batas_harga) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="p-3">
      <Pagination :page="meta.page" :last-page="meta.lastPage" @change="load" />
    </div>
  </div>

  <ModalDialog v-model="open" title="Form">
    <form id="subcategory-form" @submit.prevent="save">
      <p v-if="formError" class="mb-3 rounded bg-red-50 px-3 py-2 text-sm text-red-700">{{ formError }}</p>

      <div class="mb-3">
        <label class="label">Kategori</label>
        <select v-model="form.category_id" class="input">
          <option value="" disabled>Pilih Kategori</option>
          <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.nama }}</option>
        </select>
        <p v-if="errors.category_id" class="err">{{ errors.category_id[0] }}</p>
      </div>

      <div class="mb-3">
        <label class="label">Nama Sub Kategori</label>
        <input v-model="form.nama" maxlength="100" class="input" />
        <p v-if="errors.nama" class="err">{{ errors.nama[0] }}</p>
      </div>

      <div>
        <label class="label">Batas Harga</label>
        <RupiahInput v-model="form.batas_harga" />
        <p v-if="errors.batas_harga" class="err">{{ errors.batas_harga[0] }}</p>
      </div>
    </form>

    <template #footer>
      <button type="button" class="btn-secondary" @click="open = false">Close</button>
      <button type="submit" form="subcategory-form" class="btn-primary" :disabled="saving">
        {{ saving ? 'Menyimpan...' : 'Submit' }}
      </button>
    </template>
  </ModalDialog>
</template>
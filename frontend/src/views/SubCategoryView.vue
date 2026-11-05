<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import http from '../api/http'
import { confirmDelete, showError } from '../utils/alert'
import { rupiah } from '../utils/format'
import Pagination from '../components/Pagination.vue'
import LoadingOverlay from '../components/LoadingOverlay.vue'
import FlashBanner from '../components/FlashBanner.vue'
import { useFlash } from '../composables/useFlash'
import ModalDialog from '../components/ModalDialog.vue'
import IconButton from '../components/IconButton.vue'
import SearchSelect from '../components/SearchSelect.vue'
import RupiahInput from '../components/RupiahInput.vue'

const rows = ref([])
const meta = reactive({ page: 1, lastPage: 1, from: 0 })
const search = ref('')
const loading = ref(true)
const flash = useFlash()
const busyId = ref(null) // id baris yang sedang dihapus / dikunci
const busy = computed(() => busyId.value !== null) // selama ada proses, tombol aksi lain dikunci
const categories = ref([])

const catName = (row) =>
  row.category?.nama ?? categories.value.find((c) => c.id === row.category_id)?.nama ?? '-'

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

let timer
watch(search, () => {
  loading.value = true
  clearTimeout(timer)
  timer = setTimeout(() => load(1), 400)
})
onMounted(() => {
  load()
  http.get('/options/categories').then(({ data }) => (categories.value = data))
})

// form modal
const open = ref(false)
const editingId = ref(null)
const form = reactive({ category_id: '', nama: '', batas_harga: 0 })
const errors = ref({})
const formError = ref('')
const saving = ref(false)

function openForm(row = null) {
  editingId.value = row?.id ?? null
  form.category_id = row?.category_id ?? ''
  form.nama = row?.nama ?? ''
  form.batas_harga = Number(row?.batas_harga ?? 0)
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
    flash.show(wasEdit ? 'Sub kategori berhasil diperbarui' : 'Sub kategori berhasil ditambahkan')
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
  if (busy.value) return
  if (!(await confirmDelete(`Sub kategori "${row.nama}"`))) return
  busyId.value = row.id
  try {
    await http.delete(`/sub-categories/${row.id}`)
    await load(rows.value.length === 1 && meta.page > 1 ? meta.page - 1 : meta.page)
    flash.show('Sub kategori berhasil dihapus')
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

      <div class="flex">
        <input v-model="search" class="input !w-56 !rounded-r-none" placeholder="Cari data.." />
        <button
          type="button"
          title="Cari"
          class="grid w-10 place-items-center rounded-r border border-l-0 border-slate-300 text-slate-500 hover:bg-slate-50"
          @click="load(1)"
        >
          <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" />
          </svg>
        </button>
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
            <th class="w-28">Action</th>
            <th>Kategori</th>
            <th>Nama Sub Kategori</th>
            <th class="text-right">Batas Harga (Rp.)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!rows.length">
            <td colspan="5" class="py-8 text-center text-slate-500">
              {{ loading ? 'Memuat data...' : 'Tidak ada data' }}
            </td>
          </tr>
          <tr v-for="(row, i) in rows" :key="row.id">
            <td>{{ meta.from + i }}</td>
            <td class="whitespace-nowrap !px-1">
              <IconButton :disabled="busy" icon="edit" title="Edit" color="text-primary hover:bg-slate-100" @click="openForm(row)" />
              <IconButton :disabled="busy" icon="trash" title="Hapus" color="text-red-500 hover:bg-red-50" :loading="busyId === row.id" @click="remove(row)" />
            </td>
            <td>{{ catName(row) }}</td>
            <td>{{ row.nama }}</td>
            <td class="text-right">{{ rupiah(row.batas_harga) }}</td>
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
    <form id="subcategory-form" @submit.prevent="save">
      <p v-if="formError" class="mb-3 rounded bg-red-50 px-3 py-2 text-sm text-red-700">{{ formError }}</p>

      <div class="mb-3">
        <label class="label">Kategori</label>
        <SearchSelect v-model="form.category_id" :options="categories" placeholder="Pilih kategori" required />
        <p v-if="errors.category_id" class="err">{{ errors.category_id[0] }}</p>
      </div>

      <div class="mb-3">
        <label class="label">Nama Sub Kategori</label>
        <input v-model="form.nama" maxlength="100" class="input" />
        <p v-if="errors.nama" class="err">{{ errors.nama[0] }}</p>
      </div>

      <div>
        <label class="label">Batas Harga</label>
        <div class="relative">
          <span class="pointer-events-none absolute left-2 top-1/2 -translate-y-1/2 text-xs text-slate-400">Rp.</span>
          <RupiahInput v-model="form.batas_harga" class="input !pl-9 text-right" />
        </div>
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
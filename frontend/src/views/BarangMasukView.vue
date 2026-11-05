<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import http from '../api/http'
import { useAuthStore } from '../stores/auth'
import { rupiah, formatDateTime, itemCode, downloadBlob } from '../utils/format'
import Pagination from '../components/Pagination.vue'
import IconButton from '../components/IconButton.vue'
import BarangMasukFormModal from '../components/BarangMasukFormModal.vue'

const auth = useAuthStore()

const rows = ref([])
const meta = reactive({ page: 1, lastPage: 1, from: 0 })
const loading = ref(false)

const filters = reactive({ category_id: '', sub_category_id: '', year: '', search: '' })
const sort = reactive({ by: 'tanggal', dir: 'desc' })

const categories = ref([])
const subCategories = ref([])
const years = ref([])

const formOpen = ref(false)
const editId = ref(null)

function params(page) {
  return {
    page,
    category_id: filters.category_id || undefined,
    sub_category_id: filters.sub_category_id || undefined,
    year: filters.year || undefined,
    search: filters.search || undefined,
    sort_by: sort.by,
    sort_dir: sort.dir,
  }
}

async function load(page = 1) {
  loading.value = true
  try {
    const { data } = await http.get('/barang-masuk', { params: params(page) })
    rows.value = data.data
    meta.page = data.current_page
    meta.lastPage = data.last_page
    meta.from = data.from ?? 0
  } finally {
    loading.value = false
  }
}

async function loadSubCategories(categoryId) {
  const { data } = await http.get('/options/sub-categories', {
    params: { category_id: categoryId || undefined },
  })
  subCategories.value = data
}

function loadYears() {
  http.get('/options/years')
    .then(({ data }) => {
      const list = data.map((y) => (typeof y === 'object' ? Object.values(y)[0] : y))
      years.value = [...new Set([new Date().getFullYear(), ...list])].sort((a, b) => b - a)
    })
    .catch(() => { years.value = [new Date().getFullYear()] })
}

onMounted(() => {
  load()
  http.get('/options/categories').then(({ data }) => (categories.value = data))
  loadYears()
  loadSubCategories('')
})

watch(() => filters.category_id, (id) => {
  filters.sub_category_id = ''
  loadSubCategories(id)
})

let timer
watch(filters, () => {
  clearTimeout(timer)
  timer = setTimeout(() => load(1), 350)
}, { deep: true })

function toggleSort(col) {
  if (sort.by === col) sort.dir = sort.dir === 'asc' ? 'desc' : 'asc'
  else { sort.by = col; sort.dir = 'asc' }
  load(1)
}
const arrow = (col) => (sort.by === col ? (sort.dir === 'asc' ? '▲' : '▼') : '')

function openForm(id = null) {
  editId.value = id
  formOpen.value = true
}
function onSaved() {
  formOpen.value = false
  load(meta.page)
  loadYears()
}

async function exportExcel() {
  try {
    const { data } = await http.get('/barang-masuk/export', { params: params(undefined), responseType: 'blob' })
    downloadBlob(data, 'barang-masuk.xlsx')
  } catch (e) {
    alert('Gagal export data')
  }
}

async function printSurat(row) {
  const win = window.open('', '_blank')
  try {
    const { data } = await http.get(`/barang-masuk/${row.id}/print`, { responseType: 'blob' })
    win.location = URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
  } catch (e) {
    win?.close()
    alert('Gagal mencetak surat')
  }
}

async function toggleVerify(row) {
  if (!auth.isAdmin) return
  try {
    const { data } = await http.patch(`/barang-masuk/${row.id}/verify`)
    row.is_verified = data.is_verified
  } catch (e) {
    alert('Gagal mengubah status verifikasi')
  }
}

async function remove(row) {
  if (!confirm(`Hapus data barang masuk dari "${row.asal_barang}"?`)) return
  try {
    await http.delete(`/barang-masuk/${row.id}`)
    await load(rows.value.length === 1 && meta.page > 1 ? meta.page - 1 : meta.page)
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menghapus data')
  }
}
</script>

<template>
  <div class="rounded bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-2 p-3">
      <button class="btn-primary" @click="openForm(null)">+ Tambah Data</button>

      <div class="flex flex-wrap items-center gap-2">
        <select v-model="filters.category_id" class="input !w-44">
          <option value="">Semua Kategori</option>
          <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.nama }}</option>
        </select>
        <select v-model="filters.sub_category_id" class="input !w-44">
          <option value="">Semua Sub Kategori</option>
          <option v-for="s in subCategories" :key="s.id" :value="s.id">{{ s.nama }}</option>
        </select>
        <select v-model="filters.year" class="input !w-32">
          <option value="">Semua Tahun</option>
          <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>
        <input v-model="filters.search" class="input !w-52" placeholder="Cari asal barang / sub kategori" />
        <button class="btn-secondary" @click="exportExcel">Export Excel</button>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full min-w-[1100px] text-sm">
        <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
          <tr>
            <th class="w-10 px-3 py-2 text-left">No</th>
            <th class="px-3 py-2 text-left">Action</th>
            <th class="cursor-pointer select-none px-3 py-2 text-left" @click="toggleSort('tanggal')">
              Tanggal {{ arrow('tanggal') }}
            </th>
            <th class="cursor-pointer select-none px-3 py-2 text-left" @click="toggleSort('asal_barang')">
              Asal Barang {{ arrow('asal_barang') }}
            </th>
            <th class="px-3 py-2 text-left">Penerima</th>
            <th class="px-3 py-2 text-left">Unit</th>
            <th class="px-3 py-2 text-left">Kode</th>
            <th class="px-3 py-2 text-left">Nama</th>
            <th class="px-3 py-2 text-right">Harga (Rp.)</th>
            <th class="px-3 py-2 text-right">Jumlah</th>
            <th class="cursor-pointer select-none px-3 py-2 text-right" @click="toggleSort('total_harga')">
              Total (Rp.) {{ arrow('total_harga') }}
            </th>
            <th class="px-3 py-2 text-center">Status</th>
          </tr>
        </thead>

        <tbody v-if="rows.length">
          <template v-for="(row, ri) in rows" :key="row.id">
            <tr
              v-for="(item, idx) in row.items"
              :key="item.id"
              :class="[idx === 0 ? 'border-t' : '', ri % 2 ? 'bg-slate-50/60' : '']"
            >
              <template v-if="idx === 0">
                <td :rowspan="row.items.length" class="px-3 py-2 align-top">{{ meta.from + ri }}</td>
                <td :rowspan="row.items.length" class="whitespace-nowrap px-2 py-2 align-top">
                  <IconButton icon="edit" title="Edit" color="text-primary hover:bg-slate-100" @click="openForm(row.id)" />
                  <IconButton icon="print" title="Cetak surat masuk" @click="printSurat(row)" />
                  <IconButton icon="trash" title="Hapus" color="text-red-600 hover:bg-red-50" @click="remove(row)" />
                </td>
                <td :rowspan="row.items.length" class="whitespace-nowrap px-3 py-2 align-top">{{ formatDateTime(row.tanggal) }}</td>
                <td :rowspan="row.items.length" class="px-3 py-2 align-top">{{ row.asal_barang }}</td>
                <td :rowspan="row.items.length" class="px-3 py-2 align-top">{{ row.user?.name }}</td>
                <td :rowspan="row.items.length" class="px-3 py-2 align-top">{{ row.sub_category?.nama }}</td>
              </template>

              <td class="px-3 py-2">{{ itemCode(row, item) }}</td>
              <td class="px-3 py-2">{{ item.nama_barang }}</td>
              <td class="px-3 py-2 text-right">{{ rupiah(item.harga) }}</td>
              <td class="whitespace-nowrap px-3 py-2 text-right">{{ item.jumlah }} {{ item.satuan }}</td>
              <td class="px-3 py-2 text-right">{{ rupiah(item.total) }}</td>

              <td v-if="idx === 0" :rowspan="row.items.length" class="px-3 py-2 text-center align-top">
                <button
                  :disabled="!auth.isAdmin"
                  :title="auth.isAdmin ? 'Klik untuk mengubah status verifikasi' : 'Hanya admin yang dapat memverifikasi'"
                  :class="[
                    'inline-grid h-6 w-6 place-items-center rounded-full border text-sm',
                    row.is_verified ? 'border-green-500 bg-green-50 text-green-600' : 'border-slate-300 text-slate-300',
                    auth.isAdmin ? 'cursor-pointer' : 'cursor-default',
                  ]"
                  @click="toggleVerify(row)"
                >
                  ✓
                </button>
              </td>
            </tr>
          </template>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="12" class="px-3 py-10 text-center text-slate-400">
              {{ loading ? 'Memuat data...' : 'Tidak ada data' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="p-3">
      <Pagination :page="meta.page" :last-page="meta.lastPage" @change="load" />
    </div>

    <BarangMasukFormModal :show="formOpen" :edit-id="editId" @close="formOpen = false" @saved="onSaved" />
  </div>
</template>
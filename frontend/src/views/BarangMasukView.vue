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
const cell = 'border border-slate-200 px-3 py-2'
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
    <!-- toolbar -->
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

        <div class="flex">
          <input v-model="filters.search" class="input !w-44 !rounded-r-none" placeholder="Cari data.." />
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

        <button class="btn-secondary" @click="exportExcel">Export Excel</button>
      </div>
    </div>

    <!-- tabel -->
    <div class="overflow-x-auto">
      <table class="w-full min-w-[1100px] border-collapse text-xs">
        <thead>
          <tr class="text-[11px] font-bold uppercase text-slate-700">
            <th :class="[cell, 'w-10 text-left']">No</th>
            <th :class="[cell, 'text-left']">Action</th>
            <th :class="[cell, 'cursor-pointer select-none text-left']" @click="toggleSort('tanggal')">
              Tanggal {{ arrow('tanggal') }}
            </th>
            <th :class="[cell, 'cursor-pointer select-none text-left']" @click="toggleSort('asal_barang')">
              Asal Barang {{ arrow('asal_barang') }}
            </th>
            <th :class="[cell, 'text-left']">Penerima</th>
            <th :class="[cell, 'text-left']">Unit</th>
            <th :class="[cell, 'text-left']">Kode</th>
            <th :class="[cell, 'text-left']">Nama</th>
            <th :class="[cell, 'text-right']">Harga (Rp.)</th>
            <th :class="[cell, 'text-right']">Jumlah</th>
            <th :class="[cell, 'cursor-pointer select-none text-right']" @click="toggleSort('total_harga')">
              Total (Rp.) {{ arrow('total_harga') }}
            </th>
            <th :class="[cell, 'text-center']">Status</th>
          </tr>
        </thead>

        <tbody v-if="rows.length">
          <template v-for="(row, ri) in rows" :key="row.id">
            <tr v-for="(item, idx) in row.items" :key="item.id">
              <template v-if="idx === 0">
                <td :rowspan="row.items.length" :class="[cell, 'align-top']">{{ meta.from + ri }}</td>
                <td :rowspan="row.items.length" :class="[cell, 'whitespace-nowrap !px-1 align-top']">
                  <IconButton icon="edit" title="Edit" @click="openForm(row.id)" />
                  <IconButton icon="print" title="Cetak surat masuk" @click="printSurat(row)" />
                  <IconButton icon="trash" title="Hapus" color="text-red-500 hover:bg-red-50" @click="remove(row)" />
                </td>
                <td :rowspan="row.items.length" :class="[cell, 'whitespace-nowrap align-top']">{{ formatDateTime(row.tanggal) }}</td>
                <td :rowspan="row.items.length" :class="[cell, 'align-top']">{{ row.asal_barang }}</td>
                <td :rowspan="row.items.length" :class="[cell, 'align-top']">{{ row.user?.name }}</td>
                <td :rowspan="row.items.length" :class="[cell, 'align-top']">{{ row.sub_category?.nama }}</td>
              </template>

              <!-- baris item: selang-seling putih / abu-abu -->
              <td :class="[cell, idx % 2 ? 'bg-slate-100' : '']">{{ itemCode(row, item) }}</td>
              <td :class="[cell, idx % 2 ? 'bg-slate-100' : '']">{{ item.nama_barang }}</td>
              <td :class="[cell, 'text-right', idx % 2 ? 'bg-slate-100' : '']">{{ rupiah(item.harga) }}</td>
              <td :class="[cell, 'whitespace-nowrap text-right', idx % 2 ? 'bg-slate-100' : '']">{{ item.jumlah }} {{ item.satuan }}</td>
              <td :class="[cell, 'text-right', idx % 2 ? 'bg-slate-100' : '']">{{ rupiah(item.total) }}</td>

              <td v-if="idx === 0" :rowspan="row.items.length" :class="[cell, 'text-center align-top']">
                <button
                  type="button"
                  :disabled="!auth.isAdmin"
                  :title="auth.isAdmin ? 'Klik untuk mengubah status verifikasi' : row.is_verified ? 'Terverifikasi' : 'Belum diverifikasi'"
                  :class="[row.is_verified ? 'text-green-500' : 'text-slate-400', auth.isAdmin ? 'cursor-pointer' : 'cursor-default']"
                  @click="toggleVerify(row)"
                >
                  <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <polyline v-if="row.is_verified" points="8 12.5 11 15.5 16 9" />
                    <line v-else x1="8" y1="12" x2="16" y2="12" />
                  </svg>
                </button>
              </td>
            </tr>
          </template>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="12" :class="[cell, 'py-10 text-center text-slate-400']">
              {{ loading ? 'Memuat data...' : 'Tidak ada data' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="border-t bg-slate-50 p-3">
      <Pagination :page="meta.page" :last-page="meta.lastPage" @change="load" />
    </div>

    <BarangMasukFormModal :show="formOpen" :edit-id="editId" @close="formOpen = false" @saved="onSaved" />
  </div>
</template>
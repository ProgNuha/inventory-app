<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '../api/http'
import { useAuthStore } from '../stores/auth'
import { rupiah } from '../utils/format'
import RupiahInput from '../components/RupiahInput.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const id = route.params.id
const isEdit = !!id

const operators = ref([])
const categories = ref([])
const subCategories = ref([])

const form = reactive({
  user_id: auth.isAdmin ? '' : auth.user?.id,
  category_id: '',
  sub_category_id: '',
  asal_barang: '',
  nomor_surat: '',
})
const items = ref([newItem()])
const lampiran = ref(null)
const existingLampiran = ref(null)

const errors = ref({})
const message = ref('')
const saving = ref(false)

function newItem() {
  return { nama_barang: '', harga: 0, jumlah: 1, satuan: '', tgl_expired: '' }
}
const lineTotal = (it) => (Number(it.harga) || 0) * (Number(it.jumlah) || 0)

const selectedSub = computed(() => subCategories.value.find((s) => s.id === form.sub_category_id))
const batasHarga = computed(() => selectedSub.value?.batas_harga ?? 0)
const grandTotal = computed(() => items.value.reduce((sum, it) => sum + lineTotal(it), 0))
const overLimit = computed(() => !!selectedSub.value && grandTotal.value > batasHarga.value)

async function loadSubCategories(categoryId) {
  if (!categoryId) {
    subCategories.value = []
    return
  }
  const { data } = await http.get('/options/sub-categories', { params: { category_id: categoryId } })
  subCategories.value = data
}

// ganti kategori -> pilihan sub kategori direset (saat prefill edit, reset dilewati)
let prefilling = false
watch(() => form.category_id, async (cid) => {
  if (!prefilling) form.sub_category_id = ''
  await loadSubCategories(cid)
})

onMounted(async () => {
  http.get('/options/categories').then(({ data }) => (categories.value = data))
  if (auth.isAdmin) http.get('/options/operators').then(({ data }) => (operators.value = data))

  if (isEdit) {
    prefilling = true
    const { data: res } = await http.get(`/barang-masuk/${id}`)
    const d = res.data ?? res
    form.user_id = d.user_id
    form.asal_barang = d.asal_barang
    form.nomor_surat = d.nomor_surat ?? ''
    existingLampiran.value = d.lampiran_url
    items.value = d.items.map((i) => ({
      nama_barang: i.nama_barang,
      harga: Number(i.harga),
      jumlah: Number(i.jumlah),
      satuan: i.satuan,
      tgl_expired: i.tgl_expired ? String(i.tgl_expired).slice(0, 10) : '',
    }))
    const catId = d.sub_category?.category_id
    await loadSubCategories(catId)
    form.category_id = catId
    form.sub_category_id = d.sub_category_id
    prefilling = false
  }
})

const addItem = () => items.value.push(newItem())
const removeItem = (i) => { if (items.value.length > 1) items.value.splice(i, 1) }
const onFile = (e) => (lampiran.value = e.target.files[0] ?? null)

// pesan error dari backend: key seperti "items.0.harga"
const err = (key) => errors.value[key]?.[0]

async function submit() {
  errors.value = {}
  message.value = ''

  if (overLimit.value) {
    message.value = `Total harga (Rp ${rupiah(grandTotal.value)}) melebihi batas harga sub kategori (Rp ${rupiah(batasHarga.value)})`
    return
  }

  const fd = new FormData()
  fd.append('user_id', form.user_id)
  fd.append('sub_category_id', form.sub_category_id)
  fd.append('asal_barang', form.asal_barang)
  if (form.nomor_surat) fd.append('nomor_surat', form.nomor_surat)
  if (lampiran.value) fd.append('lampiran', lampiran.value)

  items.value.forEach((it, i) => {
    fd.append(`items[${i}][nama_barang]`, it.nama_barang)
    fd.append(`items[${i}][harga]`, it.harga)
    fd.append(`items[${i}][jumlah]`, it.jumlah)
    fd.append(`items[${i}][satuan]`, it.satuan)
    if (it.tgl_expired) fd.append(`items[${i}][tgl_expired]`, it.tgl_expired)
  })

  saving.value = true
  try {
    if (isEdit) {
      fd.append('_method', 'PUT') // PHP tidak membaca multipart pada method PUT
      await http.post(`/barang-masuk/${id}`, fd)
    } else {
      await http.post('/barang-masuk', fd)
    }
    router.push('/barang-masuk')
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
      message.value = e.response.data.message ?? 'Data tidak valid'
    } else {
      message.value = e.response?.data?.message || 'Gagal menyimpan data'
    }
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <form class="rounded bg-white p-5 shadow-sm" @submit.prevent="submit">
    <h2 class="mb-4 text-lg font-semibold">{{ isEdit ? 'Edit' : 'Tambah' }} Barang Masuk</h2>

    <div v-if="message" class="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
      {{ message }}
    </div>

    <div class="grid gap-4 md:grid-cols-2">
      <div>
        <label class="mb-1 block text-sm font-medium">Operator</label>
        <select v-if="auth.isAdmin" v-model="form.user_id" class="input" required>
          <option value="" disabled>Pilih operator</option>
          <option v-for="o in operators" :key="o.id" :value="o.id">{{ o.name }}</option>
        </select>
        <input v-else :value="auth.user?.name" class="input bg-slate-100" disabled />
        <p v-if="err('user_id')" class="mt-1 text-xs text-red-600">{{ err('user_id') }}</p>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium">Kategori</label>
        <select v-model="form.category_id" class="input" required>
          <option value="" disabled>Pilih kategori</option>
          <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.kode }} - {{ c.nama }}</option>
        </select>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium">Sub Kategori</label>
        <select v-model="form.sub_category_id" class="input" required :disabled="!form.category_id">
          <option value="" disabled>Pilih sub kategori</option>
          <option v-for="s in subCategories" :key="s.id" :value="s.id">{{ s.nama }}</option>
        </select>
        <p v-if="err('sub_category_id')" class="mt-1 text-xs text-red-600">{{ err('sub_category_id') }}</p>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium">Batas Harga</label>
        <input :value="selectedSub ? 'Rp ' + rupiah(batasHarga) : ''" class="input bg-slate-100" disabled />
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium">Asal Barang</label>
        <input v-model="form.asal_barang" class="input" maxlength="200" required />
        <p v-if="err('asal_barang')" class="mt-1 text-xs text-red-600">{{ err('asal_barang') }}</p>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium">Nomor Surat <span class="text-slate-400">(opsional)</span></label>
        <input v-model="form.nomor_surat" class="input" maxlength="100" />
        <p v-if="err('nomor_surat')" class="mt-1 text-xs text-red-600">{{ err('nomor_surat') }}</p>
      </div>

      <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium">Lampiran <span class="text-slate-400">(doc, docx, zip - opsional)</span></label>
        <input type="file" accept=".doc,.docx,.zip" class="input" @change="onFile" />
        <p v-if="isEdit && existingLampiran" class="mt-1 text-xs text-slate-500">
          Lampiran saat ini:
          <a :href="existingLampiran" target="_blank" class="text-primary hover:underline">lihat file</a>
          (pilih file baru untuk mengganti)
        </p>
        <p v-if="err('lampiran')" class="mt-1 text-xs text-red-600">{{ err('lampiran') }}</p>
      </div>
    </div>

    <!-- Baris item dinamis -->
    <div class="mt-6">
      <div class="mb-2 flex items-center justify-between">
        <h3 class="font-medium">Daftar Barang</h3>
        <button type="button" class="btn-secondary" @click="addItem">+ Tambah Baris</button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-sm">
          <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
            <tr>
              <th class="px-2 py-2 text-left">Nama Barang</th>
              <th class="px-2 py-2 text-left w-40">Harga (Rp)</th>
              <th class="px-2 py-2 text-left w-24">Jumlah</th>
              <th class="px-2 py-2 text-left w-28">Satuan</th>
              <th class="px-2 py-2 text-left w-40">Total (Rp)</th>
              <th class="px-2 py-2 text-left w-40">Tgl Expired</th>
              <th class="w-10"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(it, i) in items" :key="i" class="align-top">
              <td class="px-2 py-1">
                <input v-model="it.nama_barang" class="input" maxlength="200" required />
                <p v-if="err(`items.${i}.nama_barang`)" class="mt-1 text-xs text-red-600">{{ err(`items.${i}.nama_barang`) }}</p>
              </td>
              <td class="px-2 py-1">
                <RupiahInput v-model="it.harga" class="input" />
                <p v-if="err(`items.${i}.harga`)" class="mt-1 text-xs text-red-600">{{ err(`items.${i}.harga`) }}</p>
              </td>
              <td class="px-2 py-1">
                <input v-model.number="it.jumlah" type="number" min="1" class="input" required />
                <p v-if="err(`items.${i}.jumlah`)" class="mt-1 text-xs text-red-600">{{ err(`items.${i}.jumlah`) }}</p>
              </td>
              <td class="px-2 py-1">
                <input v-model="it.satuan" class="input" maxlength="40" required />
                <p v-if="err(`items.${i}.satuan`)" class="mt-1 text-xs text-red-600">{{ err(`items.${i}.satuan`) }}</p>
              </td>
              <td class="px-2 py-1">
                <input :value="rupiah(lineTotal(it))" class="input bg-slate-100 text-right" disabled />
              </td>
              <td class="px-2 py-1">
                <input v-model="it.tgl_expired" type="date" class="input" />
                <p v-if="err(`items.${i}.tgl_expired`)" class="mt-1 text-xs text-red-600">{{ err(`items.${i}.tgl_expired`) }}</p>
              </td>
              <td class="px-2 py-1 text-center">
                <button
                  type="button"
                  class="text-lg text-red-500 disabled:opacity-30"
                  :disabled="items.length === 1"
                  title="Hapus baris"
                  @click="removeItem(i)"
                >
                  ×
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-3 text-right text-sm">
        <span class="text-slate-500">Total keseluruhan:</span>
        <strong :class="overLimit ? 'text-red-600' : ''"> Rp {{ rupiah(grandTotal) }}</strong>
        <span v-if="selectedSub" class="text-slate-400"> / batas Rp {{ rupiah(batasHarga) }}</span>
        <p v-if="overLimit" class="mt-1 text-xs text-red-600">Total melebihi batas harga sub kategori.</p>
        <p v-if="err('items')" class="mt-1 text-xs text-red-600">{{ err('items') }}</p>
      </div>
    </div>

    <div class="mt-6 flex justify-end gap-2">
      <RouterLink to="/barang-masuk" class="btn-secondary inline-block">Kembali</RouterLink>
      <button type="submit" class="btn-primary" :disabled="saving">
        {{ saving ? 'Menyimpan...' : 'Simpan' }}
      </button>
    </div>
  </form>
</template>
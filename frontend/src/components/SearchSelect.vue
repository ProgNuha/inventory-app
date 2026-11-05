<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  // array objek ({id, nama}) atau array primitif (mis. tahun)
  options: { type: Array, default: () => [] },
  labelKey: { type: String, default: 'nama' },
  valueKey: { type: String, default: 'id' },
  placeholder: { type: String, default: 'Pilih...' },
  // jika diisi, muncul pilihan pertama bernilai '' (dipakai untuk filter, mis. "Semua Kategori")
  emptyLabel: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue', 'change'])

const root = ref(null)
const input = ref(null)
const list = ref(null)
const open = ref(false)
const query = ref('')
const active = ref(0)

const items = computed(() => {
  const mapped = props.options.map((o) =>
    o !== null && typeof o === 'object'
      ? { value: o[props.valueKey], label: String(o[props.labelKey]) }
      : { value: o, label: String(o) },
  )
  return props.emptyLabel ? [{ value: '', label: props.emptyLabel }, ...mapped] : mapped
})
const selected = computed(() => items.value.find((i) => i.value === props.modelValue))
const filtered = computed(() => {
  const q = query.value.trim().toLowerCase()
  return q ? items.value.filter((i) => i.label.toLowerCase().includes(q)) : items.value
})
// teks pada tombol: opsi terpilih, atau placeholder bila belum memilih
const display = computed(() => (selected.value && selected.value.value !== '' ? selected.value.label : null))
const buttonText = computed(() => display.value ?? (props.emptyLabel || props.placeholder))

watch(query, () => (active.value = 0))
watch(() => props.disabled, (d) => d && (open.value = false))

async function show() {
  if (props.disabled) return
  if (open.value) { open.value = false; return }
  open.value = true
  query.value = ''
  active.value = Math.max(0, filtered.value.findIndex((i) => i.value === props.modelValue))
  await nextTick()
  input.value?.focus()
  scrollActive()
}
function scrollActive() {
  nextTick(() => list.value?.children[active.value]?.scrollIntoView({ block: 'nearest' }))
}
function move(step) {
  if (!filtered.value.length) return
  active.value = (active.value + step + filtered.value.length) % filtered.value.length
  scrollActive()
}
function pick(item) {
  if (!item) return
  if (item.value !== props.modelValue) {
    emit('update:modelValue', item.value)
    emit('change', item.value)
  }
  open.value = false
}
function onClickOutside(e) {
  if (root.value && !root.value.contains(e.target)) open.value = false
}
onMounted(() => document.addEventListener('mousedown', onClickOutside))
onBeforeUnmount(() => document.removeEventListener('mousedown', onClickOutside))
</script>

<template>
  <div ref="root" class="relative">
    <button
      type="button"
      class="input flex items-center justify-between gap-2 text-left"
      :class="{ 'cursor-not-allowed !border-slate-200 !bg-slate-200 opacity-70': disabled }"
      :disabled="disabled"
      @click="show"
    >
      <span class="truncate" :class="display && !disabled ? 'text-slate-900' : 'text-slate-400'">{{ buttonText }}</span>
      <svg
        viewBox="0 0 24 24"
        class="h-4 w-4 shrink-0 text-slate-400 transition-transform"
        :class="{ 'rotate-180': open }"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
      >
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </button>

    <!-- input tersembunyi agar atribut required memicu validasi bawaan browser -->
    <input
      v-if="required"
      :value="modelValue"
      required
      tabindex="-1"
      aria-hidden="true"
      class="pointer-events-none absolute inset-0 h-full w-full opacity-0"
    />

    <div v-if="open" class="absolute z-30 mt-1 w-full min-w-44 rounded border border-slate-300 bg-white shadow-lg">
      <div class="border-b p-2">
        <input
          ref="input"
          v-model="query"
          class="input"
          placeholder="Cari..."
          autocomplete="off"
          @keydown.esc.prevent="open = false"
          @keydown.down.prevent="move(1)"
          @keydown.up.prevent="move(-1)"
          @keydown.enter.prevent="pick(filtered[active])"
        />
      </div>
      <ul ref="list" class="max-h-52 overflow-y-auto py-1 text-sm">
        <li
          v-for="(item, i) in filtered"
          :key="item.value === '' ? '__empty' : item.value"
          class="cursor-pointer px-3 py-1.5"
          :class="[
            i === active ? 'bg-slate-100' : '',
            item.value === modelValue ? 'font-semibold text-primary' : '',
          ]"
          @mouseenter="active = i"
          @click="pick(item)"
        >
          {{ item.label }}
        </li>
        <li v-if="!filtered.length" class="px-3 py-2 text-slate-400">Tidak ditemukan</li>
      </ul>
    </div>
  </div>
</template>

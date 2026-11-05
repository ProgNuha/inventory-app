<script setup>
import { computed } from 'vue'

const props = defineProps({ page: Number, lastPage: Number })
const emit = defineEmits(['change'])

// tampilkan maksimal 5 nomor di sekitar halaman aktif
const pages = computed(() => {
  const total = Math.max(props.lastPage || 1, 1)
  let start = Math.max(1, props.page - 2)
  const end = Math.min(total, start + 4)
  start = Math.max(1, end - 4)
  return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})

function go(p) {
  if (p >= 1 && p <= props.lastPage && p !== props.page) emit('change', p)
}
</script>

<template>
  <nav class="flex items-center justify-end gap-1 text-sm text-slate-600">
    <button v-if="page > 1" class="px-2 py-1 hover:text-primary" @click="go(page - 1)">‹ prev</button>
    <button
      v-for="p in pages"
      :key="p"
      :class="[
        'min-w-8 rounded px-2 py-1',
        p === page ? 'bg-primary text-white' : 'hover:bg-slate-200',
      ]"
      @click="go(p)"
    >
      {{ p }}
    </button>
    <button v-if="page < lastPage" class="px-2 py-1 hover:text-primary" @click="go(page + 1)">next ›</button>
  </nav>
</template>
<script setup>
import { computed } from 'vue'

const props = defineProps({ page: Number, lastPage: Number })
const emit = defineEmits(['change'])

const pages = computed(() => {
  const out = []
  const start = Math.max(1, props.page - 2)
  const end = Math.min(props.lastPage, props.page + 2)
  for (let i = start; i <= end; i++) out.push(i)
  return out
})
</script>

<template>
  <div v-if="lastPage > 1" class="flex items-center justify-end gap-1 text-sm">
    <button class="px-2 py-1 rounded hover:bg-slate-100 disabled:opacity-40" :disabled="page <= 1" @click="emit('change', page - 1)">
      ‹ Prev
    </button>
    <button
      v-for="p in pages" :key="p"
      :class="['px-2.5 py-1 rounded', p === page ? 'bg-primary text-white' : 'hover:bg-slate-100']"
      @click="emit('change', p)"
    >
      {{ p }}
    </button>
    <button class="px-2 py-1 rounded hover:bg-slate-100 disabled:opacity-40" :disabled="page >= lastPage" @click="emit('change', page + 1)">
      Next ›
    </button>
  </div>
</template>
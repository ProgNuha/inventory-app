<script setup>
import { ref, watch } from 'vue'

const props = defineProps({ message: { type: String, default: '' } })
const emit = defineEmits(['close'])

// simpan teks terakhir agar tidak kosong saat animasi menutup berjalan
const text = ref(props.message)
watch(() => props.message, (m) => { if (m) text.value = m })
</script>

<template>
  <!-- grid 0fr -> 1fr: tinggi menutup halus, tabel di bawahnya tidak melompat -->
  <div
    class="grid transition-[grid-template-rows,opacity] duration-300 ease-out"
    :class="message ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'"
    :aria-hidden="!message"
  >
    <div class="overflow-hidden">
      <div class="px-3 pb-3">
        <div
          class="flex items-center gap-2 rounded border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800"
          role="status"
          aria-live="polite"
        >
          <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" /><polyline points="8 12.5 11 15.5 16 9" />
          </svg>
          <span class="flex-1">{{ text }}</span>
          <button
            type="button"
            class="grid h-5 w-5 place-items-center rounded text-green-700 hover:bg-green-100"
            title="Tutup"
            aria-label="Tutup"
            :tabindex="message ? 0 : -1"
            @click="emit('close')"
          >
            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
              <line x1="6" y1="6" x2="18" y2="18" /><line x1="18" y1="6" x2="6" y2="18" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

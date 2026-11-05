<script setup>
// Spinner di tengah area (induk harus `relative`). Tampil saat `show` true.
defineProps({ show: Boolean })
</script>

<template>
  <Transition
    enter-active-class="transition-opacity duration-100"
    leave-active-class="transition-opacity duration-150"
    enter-from-class="opacity-0"
    leave-to-class="opacity-0"
  >
    <div v-if="show" class="pointer-events-none absolute inset-0 z-10 grid place-items-center" role="status" aria-label="Memuat">
      <span class="h-8 w-8 animate-spin rounded-full border-[3px] border-slate-300 border-t-primary"></span>
    </div>
  </Transition>
</template>

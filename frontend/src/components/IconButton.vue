<script setup>
defineProps({
  icon: { type: String, required: true },
  title: { type: String, default: '' },
  color: { type: String, default: 'text-slate-600 hover:bg-slate-100' },
})
</script>

<template>
  <button
    type="button"
    :title="title"
    :aria-label="title"
    :class="['inline-grid h-6 w-6 place-items-center rounded transition-colors', color]"
  >
    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <template v-if="icon === 'edit'">
        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" />
      </template>
      <template v-else-if="icon === 'print'">
        <polyline points="6 9 6 2 18 2 18 9" />
        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
        <rect x="6" y="14" width="12" height="8" />
      </template>
      <template v-else-if="icon === 'trash'">
        <polyline points="3 6 5 6 21 6" />
        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
        <path d="M10 11v6M14 11v6" />
        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
      </template>
      <template v-else-if="icon === 'lock'">
        <rect x="3" y="11" width="18" height="11" rx="2" />
        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
      </template>
      <template v-else-if="icon === 'unlock'">
        <rect x="3" y="11" width="18" height="11" rx="2" />
        <path d="M7 11V7a5 5 0 0 1 9.9-1" />
      </template>
    </svg>
  </button>
</template>
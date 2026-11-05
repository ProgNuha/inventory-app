<script setup>
defineProps({ modelValue: Boolean, title: String, wide: Boolean })
defineEmits(['update:modelValue'])
</script>

<template>
  <div
    v-if="modelValue"
    class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4"
    @click.self="$emit('update:modelValue', false)"
  >
    <div :class="['mt-16 w-full rounded bg-white shadow-lg', wide ? 'max-w-2xl' : 'max-w-md']">
      <div class="flex items-center justify-between border-b px-4 py-3">
        <h3 class="text-sm font-semibold">{{ title }}</h3>
        <button class="text-slate-400 hover:text-slate-600" @click="$emit('update:modelValue', false)">✕</button>
      </div>
      <div class="p-4"><slot /></div>
      <div v-if="$slots.footer" class="flex justify-end gap-2 border-t px-4 py-3">
        <slot name="footer" />
      </div>
    </div>
  </div>
</template>
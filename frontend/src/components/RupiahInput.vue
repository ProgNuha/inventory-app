<script setup>
import { computed } from 'vue'

const model = defineModel() // nilai asli berupa angka (atau '' jika kosong)

const display = computed(() =>
  model.value === '' || model.value === null || model.value === undefined
    ? ''
    : Number(model.value).toLocaleString('id-ID'),
)

function onInput(e) {
  const digits = e.target.value.replace(/\D/g, '')
  model.value = digits === '' ? '' : Number(digits)
  e.target.value = digits === '' ? '' : Number(digits).toLocaleString('id-ID')
}
</script>

<template>
  <input :value="display" inputmode="numeric" class="input text-right" @input="onInput" />
</template>
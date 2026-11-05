import { ref, onBeforeUnmount } from 'vue'

// Pesan singkat di atas tabel yang hilang sendiri setelah `duration` ms.
export function useFlash(duration = 4000) {
  const message = ref('')
  let timer

  function hide() {
    clearTimeout(timer)
    message.value = ''
  }

  function show(text) {
    clearTimeout(timer)
    message.value = text
    timer = setTimeout(hide, duration)
  }

  onBeforeUnmount(() => clearTimeout(timer))

  return { message, show, hide }
}

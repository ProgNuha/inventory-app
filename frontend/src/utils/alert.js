import Swal from 'sweetalert2'

// warna mengikuti tema (lihat --color-primary di style.css)
const PRIMARY = '#2f7a9e'
const DANGER = '#dc2626'

const base = Swal.mixin({
  reverseButtons: true,
  buttonsStyling: true,
  cancelButtonText: 'Batal',
  cancelButtonColor: '#64748b',
  customClass: { popup: 'text-sm' },
})

// Dialog konfirmasi. Resolve true bila pengguna menekan tombol konfirmasi.
export async function confirmDialog({ title, text, confirmText = 'Ya', danger = false }) {
  const result = await base.fire({
    icon: danger ? 'warning' : 'question',
    title,
    text,
    showCancelButton: true,
    confirmButtonText: confirmText,
    confirmButtonColor: danger ? DANGER : PRIMARY,
    focusCancel: danger, // aksi berbahaya: fokus awal di "Batal"
  })
  return result.isConfirmed
}

export const confirmDelete = (name) =>
  confirmDialog({
    title: 'Hapus data?',
    text: name ? `${name} akan dihapus dan tidak dapat dikembalikan.` : 'Data akan dihapus dan tidak dapat dikembalikan.',
    confirmText: 'Ya, hapus',
    danger: true,
  })

// Pesan kesalahan: dialog yang harus ditutup pengguna agar tidak terlewat.
export function showError(message, title = 'Terjadi kesalahan') {
  return base.fire({
    icon: 'error',
    title,
    text: message,
    confirmButtonText: 'OK',
    confirmButtonColor: PRIMARY,
  })
}

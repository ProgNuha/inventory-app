export const rupiah = (n) => Number(n ?? 0).toLocaleString('id-ID')

// "05/11/2026 20:49:49" (zona waktu browser)
export function formatDateTime(value) {
  if (!value) return '-'
  return new Date(value)
    .toLocaleString('en-GB', {
      day: '2-digit', month: '2-digit', year: 'numeric',
      hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false,
    })
    .replace(',', '')
}

// Kode barang: kode kategori + nomor item, mis. PK0002 (sama dengan di export Excel)
export const itemCode = (row, item) =>
  `${row.sub_category?.category?.kode ?? ''}${String(item.id).padStart(4, '0')}`

export function downloadBlob(blob, filename) {
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  document.body.appendChild(a)
  a.click()
  a.remove()
  setTimeout(() => URL.revokeObjectURL(url), 1000)
}
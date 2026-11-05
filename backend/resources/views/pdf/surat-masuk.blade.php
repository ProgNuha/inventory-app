<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Barang Masuk</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { text-align: center; font-size: 16px; margin: 0 0 4px; }
        .sub { text-align: center; margin-bottom: 18px; }
        table.info td { padding: 2px 8px 2px 0; vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 14px; }
        table.items th, table.items td { border: 1px solid #444; padding: 5px 6px; }
        table.items th { background: #eee; }
        .r { text-align: right; }
        table.sign { width: 100%; margin-top: 48px; }
        table.sign td { width: 50%; text-align: center; }
    </style>
</head>
<body>
    <h1>SURAT BARANG MASUK</h1>
    <div class="sub">Aplikasi Pengelolaan Barang</div>

    <table class="info">
        <tr><td>Nomor Surat</td><td>:</td><td>{{ $barangMasuk->nomor_surat ?? '-' }}</td></tr>
        <tr><td>Tanggal</td><td>:</td><td>{{ $barangMasuk->tanggal->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Asal Barang</td><td>:</td><td>{{ $barangMasuk->asal_barang }}</td></tr>
        <tr><td>Penerima</td><td>:</td><td>{{ $barangMasuk->user->name }}</td></tr>
        <tr><td>Kategori</td><td>:</td><td>{{ $barangMasuk->subCategory->category->nama }} / {{ $barangMasuk->subCategory->nama }}</td></tr>
        <tr><td>Status</td><td>:</td><td>{{ $barangMasuk->is_verified ? 'Terverifikasi' : 'Belum diverifikasi' }}</td></tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>No</th><th>Nama Barang</th><th>Harga (Rp)</th><th>Jumlah</th><th>Total (Rp)</th><th>Tgl. Expired</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($barangMasuk->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td class="r">{{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="r">{{ $item->jumlah }} {{ $item->satuan }}</td>
                    <td class="r">{{ number_format($item->total, 0, ',', '.') }}</td>
                    <td>{{ $item->tgl_expired ? \Carbon\Carbon::parse($item->tgl_expired)->format('d/m/Y') : '-' }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" class="r"><strong>Total</strong></td>
                <td class="r"><strong>{{ number_format($barangMasuk->total_harga, 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <table class="sign">
        <tr><td>Pengirim</td><td>Penerima</td></tr>
        <tr><td><br><br><br>( ............... )</td><td><br><br><br>( {{ $barangMasuk->user->name }} )</td></tr>
    </table>
</body>
</html>
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasukItem extends Model
{
    protected $fillable = ['barang_masuk_id', 'nama_barang', 'harga', 'jumlah', 'satuan', 'total', 'tgl_expired'];
    public function barangMasuk()
    {
        return $this->belongsTo(BarangMasuk::class);
    }

}

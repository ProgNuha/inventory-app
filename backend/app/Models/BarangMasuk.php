<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BarangMasuk extends Model
{
    protected $fillable = ['user_id', 'sub_category_id', 'tanggal', 'asal_barang', 'nomor_surat', 'lampiran', 'is_verified'];
    protected $casts = ['tanggal' => 'datetime', 'is_verified' => 'boolean'];
    protected $appends = ['lampiran_url'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
    public function items()
    {
        return $this->hasMany(BarangMasukItem::class);
    }

    protected function lampiranUrl(): Attribute
    {
        return Attribute::get(
            fn() => $this->lampiran ? '/storage/' . $this->lampiran : null
        );
    }
}

<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BarangMasukExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function __construct(private array $rows) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['NO', 'TANGGAL', 'ASAL BARANG', 'PENERIMA', 'UNIT', 'KODE', 'NAMA', 'HARGA (RP.)', 'JUMLAH', 'TOTAL (RP.)', 'STATUS'];
    }
}
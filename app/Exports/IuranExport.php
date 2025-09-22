<?php
// app/Exports/IuranExport.php

namespace App\Exports;

use App\Models\Tagihan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting; // <-- 1. Tambahkan ini
use PhpOffice\PhpSpreadsheet\Style\NumberFormat; // <-- 2. Tambahkan ini

class IuranExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting // <-- 3. Tambahkan ini
{
    protected $tahun;
    protected $bulan;
    protected $user;

    public function __construct(int $tahun, int $bulan)
    {
        $this->tahun = $tahun;
        $this->bulan = $bulan;
        $this->user = auth()->user();
    }

    public function query()
    {
        // ... (kode query Anda tidak perlu diubah)
        $query = Tagihan::query()
            ->with(['kartuKeluarga', 'iuran'])
            ->where('tahun', $this->tahun)
            ->where('bulan', $this->bulan);
        if ($this->user->role === 'rt') {
            $query->whereHas('kartuKeluarga', function ($q) {
                $q->where('rt_id', $this->user->rt_id);
            });
        } elseif ($this->user->role === 'rw') {
            $query->whereHas('kartuKeluarga.rt', function ($q) {
                $q->where('rw_id', $this->user->rw_id);
            });
        }
        return $query;
    }

    public function headings(): array
    {
        return [
            'Nomor KK',
            'Alamat',
            'Jenis Iuran',
            'Jumlah Tagihan',
            'Status Pembayaran',
            'Tanggal Tagihan',
        ];
    }

    public function map($tagihan): array
    {
        return [
            "'" . $tagihan->kartuKeluarga->nomor_kk,
            $tagihan->kartuKeluarga->alamat,
            $tagihan->iuran->jenis_iuran,
            $tagihan->jumlah,
            $tagihan->status,
            $tagihan->tanggal_tagihan,
        ];
    }

    /**
     * Menentukan format untuk kolom tertentu.
     */
    public function columnFormats(): array // <-- 4. Tambahkan seluruh method ini
    {
        // Kolom 'A' (Nomor KK) akan diformat sebagai Teks.
        return [
            'A' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
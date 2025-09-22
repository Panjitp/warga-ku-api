<?php
// app/Models/Tagihan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Tambahkan ini

class Tagihan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kartu_keluarga_id',
        'iuran_id',
        'tanggal_tagihan',
        'tahun',
        'bulan',
        'jumlah',
        'status',
    ];

    /**
     * Mendefinisikan bahwa sebuah Tagihan dimiliki oleh satu KartuKeluarga.
     */
    public function kartuKeluarga(): BelongsTo // <-- Tambahkan fungsi ini
    {
        return $this->belongsTo(KartuKeluarga::class);
    }

    /**
     * Mendefinisikan bahwa sebuah Tagihan merujuk pada satu jenis Iuran.
     */
    public function iuran(): BelongsTo // <-- Tambahkan fungsi ini juga
    {
        return $this->belongsTo(Iuran::class);
    }
}
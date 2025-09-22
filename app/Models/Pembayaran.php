<?php
// app/Models/Pembayaran.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Tambahkan ini

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'tagihan_id',
        'warga_id',
        'tanggal_bayar',
        'metode_pembayaran',
        'bukti_pembayaran',
        'catatan',
        'diverifikasi_oleh',
        'diverifikasi_pada',
    ];

    /**
     * Mendefinisikan bahwa sebuah Pembayaran merujuk pada satu Tagihan.
     */
    public function tagihan(): BelongsTo // <-- Tambahkan fungsi ini
    {
        return $this->belongsTo(Tagihan::class);
    }

    /**
     * Mendefinisikan bahwa sebuah Pembayaran dilakukan oleh satu Warga.
     */
    public function warga(): BelongsTo // <-- Tambahkan fungsi ini juga
    {
        return $this->belongsTo(Warga::class);
    }
}
<?php

namespace App\Models; // <-- PASTIKAN INI ADA DAN BENAR

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Iuran extends Model
{
    use HasFactory;

    // Kita belum mendefinisikan $fillable, ini bisa jadi masalah nanti,
    // mari kita tambahkan sekarang.
    protected $fillable = [
        'rw_id',
        'jenis_iuran',
        'deskripsi',
        'nominal',
    ];
}
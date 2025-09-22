<?php
// app/Models/Kas.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kas extends Model
{
    use HasFactory;

    protected $table = 'kas'; // Menegaskan nama tabel karena 'Kas' bisa ambigu

    protected $fillable = [
        'rt_id',
        'jenis',
        'keterangan',
        'jumlah',
        'tanggal',
        'pembayaran_id',
    ];
}
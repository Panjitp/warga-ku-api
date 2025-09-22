<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rw extends Model
{
    use HasFactory;

    protected $table = 'rws'; // Opsional, untuk menegaskan nama tabel

    protected $fillable = [
        'nomor',
        'nama_ketua',
        'alamat_sekretariat',
        'user_id',
    ];
}

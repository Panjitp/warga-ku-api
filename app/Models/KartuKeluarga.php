<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- TAMBAHKAN INI

class KartuKeluarga extends Model
{
    use HasFactory;

    protected $table = 'kartu_keluargas';

    protected $fillable = ['nomor_kk', 'alamat', 'rt_id'];

    // Satu KK bisa punya banyak anggota (warga)
    public function wargas(): HasMany
    {
        return $this->hasMany(Warga::class);
    }

    public function rt(): BelongsTo // <-- TAMBAHKAN FUNGSI INI
    {
        return $this->belongsTo(Rt::class);
    }
}
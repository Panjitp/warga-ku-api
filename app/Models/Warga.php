<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warga extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'kartu_keluarga_id', 'nik', 'nama_lengkap', 'tempat_lahir',
        'tanggal_lahir', 'jenis_kelamin', 'agama', 'status_perkawinan',
        'pekerjaan', 'status_warga' ,'tampilkan_di_direktori'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tampilkan_di_direktori' => 'boolean', // <-- TAMBAHKAN INI
    ];

    // Satu warga hanya punya satu akun user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Satu warga tergabung dalam satu KK
    public function kartuKeluarga(): BelongsTo
    {
        return $this->belongsTo(KartuKeluarga::class);
    }
}
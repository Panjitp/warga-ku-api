<?php
// app/Models/Saran.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Tambahkan ini

class Saran extends Model
{
    use HasFactory;

    protected $table = 'sarans';

    protected $fillable = [
        'user_id',
        'judul',
        'isi',
        'status',
        'rt_id',
        'rw_id',
    ];

    /**
     * Mendefinisikan bahwa sebuah Saran dikirim oleh satu User.
     */
    public function user(): BelongsTo // <-- Tambahkan fungsi ini
    {
        return $this->belongsTo(User::class);
    }
}
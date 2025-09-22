<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rt extends Model
{
    use HasFactory;

    protected $table = 'rts';

    protected $fillable = [
        'nomor',
        'nama_ketua',
        'rw_id',
        'user_id',
    ];

    // Mendefinisikan bahwa sebuah RT dimiliki oleh satu RW
    public function rw(): BelongsTo
    {
        return $this->belongsTo(Rw::class);
    }
}
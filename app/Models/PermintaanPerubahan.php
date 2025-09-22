<?php
// app/Models/PermintaanPerubahan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanPerubahan extends Model
{
    use HasFactory;

    protected $table = 'permintaan_perubahans';

    /**
     * Pastikan semua kolom ini ada di dalam $fillable.
     */
    protected $fillable = [
        'user_id',
        'field_name',
        'old_value',
        'new_value',
        'status',
        'reviewed_by',
        'review_notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
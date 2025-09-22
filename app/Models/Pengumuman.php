<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumumans';

    protected $fillable = [
        'user_id',
        'judul',
        'isi',
        'gambar',
        'tujuan',
        'rt_id',
        'rw_id',
    ];
}

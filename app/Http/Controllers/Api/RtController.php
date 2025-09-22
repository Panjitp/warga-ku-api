<?php
// app/Http/Controllers/Api/RtController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rt; // <-- KEMUNGKINAN BESAR BARIS INI YANG HILANG
use App\Models\User;

class RtController extends Controller
{
    /**
     * Menampilkan detail informasi sebuah RT.
     */
    public function show(Rt $rt) // Tipe data "Rt" di sini yang memerlukan "use" statement
    {
        // Ambil data user ketua RT berdasarkan user_id di tabel rts
        $ketua = User::find($rt->user_id);

        return response()->json([
            'id' => $rt->id,
            'nomor' => $rt->nomor,
            'nama_ketua' => $rt->nama_ketua,
            'telepon_ketua' => $ketua ? $ketua->phone_number : 'Tidak tersedia',
        ]);
    }
}
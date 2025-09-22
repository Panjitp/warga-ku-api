<?php
// app/Http/Controllers/Api/DirektoriController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warga;
use Illuminate\Http\Request;

class DirektoriController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Ambil data Warga dengan memuat relasi User
        $wargas = Warga::with('user:id,phone_number')
            // ========================= PERBAIKAN DI SINI =========================
            // Cari Warga yang memiliki relasi 'kartuKeluarga'
            // di mana 'rt_id' di dalam kartuKeluarga tersebut sama dengan rt_id user
            ->whereHas('kartuKeluarga', function ($query) use ($user) {
                $query->where('rt_id', $user->rt_id);
            })
            // =======================================================================
            ->where('tampilkan_di_direktori', true)
            ->where('user_id', '!=', $user->id)
            ->get();

        // Format ulang hasilnya
        $direktori = $wargas->map(function ($warga) {
            return [
                'nama_lengkap' => $warga->nama_lengkap,
                'phone_number' => $warga->user ? $warga->user->phone_number : null,
            ];
        });

        return response()->json($direktori);
    }
}
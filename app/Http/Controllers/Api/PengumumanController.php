<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PengumumanController extends Controller
{
    /**
     * Endpoint untuk Warga: Menampilkan timeline pengumuman.
     */
    public function index()
    {
        $user = auth()->user();
        $rtId = $user->rt_id;
        $rwId = $user->rw_id;

        $pengumumans = Pengumuman::where(function ($query) use ($rtId, $rwId) {
                // Ambil pengumuman yang ditujukan ke RW tempat warga tinggal
                $query->where('tujuan', 'RW')->where('rw_id', $rwId);
            })
            ->orWhere(function ($query) use ($rtId) {
                // ATAU ambil pengumuman yang ditujukan ke RT tempat warga tinggal
                $query->where('tujuan', 'RT')->where('rt_id', $rtId);
            })
            ->orWhere('tujuan', 'SEMUA') // ATAU ambil pengumuman untuk semua
            ->latest() // Urutkan dari yang terbaru
            ->get();
        
        return response()->json($pengumumans);
    }

    /**
     * Endpoint untuk Pengurus: Membuat pengumuman baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'tujuan' => 'required|in:RW,RT', // Pengurus hanya bisa memilih RW atau RT
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pengurus = auth()->user();
        $rtId = null;
        $rwId = null;
        
        // Tentukan target pengumuman berdasarkan role pengurus & pilihan
        if ($pengurus->role === 'rw' && $request->tujuan === 'RW') {
            $rwId = $pengurus->rw_id;
        } elseif ($pengurus->role === 'rt' && $request->tujuan === 'RT') {
            $rtId = $pengurus->rt_id;
            $rwId = $pengurus->rw_id;
        } else {
            return response()->json(['message' => 'Anda tidak memiliki hak untuk membuat pengumuman dengan tujuan ini.'], 403);
        }

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('public/pengumuman');
        }

        $pengumuman = Pengumuman::create([
            'user_id' => $pengurus->id,
            'judul' => $request->judul,
            'isi' => $request->isi,
            'gambar' => $gambarPath,
            'tujuan' => $request->tujuan,
            'rt_id' => $rtId,
            'rw_id' => $rwId,
        ]);

        return response()->json(['message' => 'Pengumuman berhasil dipublikasikan!', 'data' => $pengumuman], 201);
    }
    
    // Anda bisa menambahkan method show, update, destroy di sini untuk pengurus.
}
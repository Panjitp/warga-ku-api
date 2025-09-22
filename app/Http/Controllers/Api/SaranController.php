<?php
// app/Http/Controllers/Api/SaranController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Saran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SaranController extends Controller
{
    /**
     * Endpoint untuk Pengurus: Menampilkan daftar saran yang masuk.
     */
    public function index()
    {
        $pengurus = auth()->user();
        $query = Saran::with('user:id,name'); // Ambil juga nama pengirim

        if ($pengurus->role === 'rt') {
            // Pengurus RT hanya melihat saran dari RT-nya
            $query->where('rt_id', $pengurus->rt_id);
        } elseif ($pengurus->role === 'rw') {
            // Pengurus RW melihat semua saran di wilayah RW-nya
            $query->where('rw_id', $pengurus->rw_id);
        }

        $sarans = $query->latest()->get();
        return response()->json($sarans);
    }

    /**
     * Endpoint untuk Warga: Mengirim saran atau laporan baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $warga = auth()->user();

        $saran = Saran::create([
            'user_id' => $warga->id,
            'judul' => $request->judul,
            'isi' => $request->isi,
            'status' => 'Dikirim',
            'rt_id' => $warga->rt_id,
            'rw_id' => $warga->rw_id,
        ]);

        return response()->json(['message' => 'Saran Anda berhasil dikirim!', 'data' => $saran], 201);
    }

    /**
     * Endpoint untuk Warga: Melihat riwayat saran yang pernah dikirim.
     */
    public function riwayat()
    {
        $riwayat = Saran::where('user_id', auth()->id())->latest()->get();
        return response()->json($riwayat);
    }

    /**
     * Endpoint untuk Pengurus: Mengubah status sebuah saran.
     */
    public function updateStatus(Request $request, Saran $saran)
    {
        // Otorisasi sederhana: pastikan pengurus dan saran berada di RW yang sama
        if (auth()->user()->rw_id !== $saran->rw_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Dibaca,Ditindaklanjuti,Selesai',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $saran->update(['status' => $request->status]);
        return response()->json(['message' => 'Status saran berhasil diperbarui.', 'data' => $saran]);
    }
}
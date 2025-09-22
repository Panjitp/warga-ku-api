<?php
// app/Http/Controllers/Api/KeuanganController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KeuanganController extends Controller
{
    // Warga melihat daftar tagihannya
    public function index()
    {
        $warga = auth()->user()->warga;
        if (!$warga) {
            return response()->json(['message' => 'Profil warga tidak ditemukan.'], 404);
        }

        $tagihans = Tagihan::with('iuran')
            ->where('kartu_keluarga_id', $warga->kartu_keluarga_id)
            ->latest()
            ->get();

        return response()->json($tagihans);
    }

    // Warga mengunggah bukti pembayaran
    public function bayar(Request $request, Tagihan $tagihan)
    {
        if (!in_array($tagihan->status, ['Belum Lunas', 'Ditolak'])) {
            return response()->json([
                'message' => 'Tagihan ini tidak bisa dibayar karena statusnya sudah ' . $tagihan->status . '.'
            ], 422); // 422 Unprocessable Entity
        }
        
        // Otorisasi: Pastikan tagihan ini milik warga yang login
        $warga = auth()->user()->warga;
        if ($tagihan->kartu_keluarga_id !== $warga->kartu_keluarga_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'metode_pembayaran' => 'required|string',
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048', // file gambar maks 2MB
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        // Simpan file bukti pembayaran
        $filePath = $request->file('bukti_pembayaran')->store('public/bukti_pembayaran');

        DB::beginTransaction();
        try {
            Pembayaran::create([
                'tagihan_id' => $tagihan->id,
                'warga_id' => $warga->id,
                'tanggal_bayar' => now(),
                'metode_pembayaran' => $request->metode_pembayaran,
                'bukti_pembayaran' => $filePath,
            ]);

            // Update status tagihan
            $tagihan->update(['status' => 'Menunggu Verifikasi']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi dari pengurus RT.']);
    }
}